<?php

namespace App\Http\Controllers;

use App\Models\Activity;
use App\Models\Communication;
use App\Models\Lead;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class WhatsAppWebhookController extends Controller
{
    /**
     * GET /webhooks/whatsapp — Meta calls this ONCE when you click "Verify and save".
     * We echo back the hub.challenge if our verify_token matches.
     */
    public function verify(Request $request)
    {
        $mode      = $request->query('hub_mode');
        $token     = $request->query('hub_verify_token');
        $challenge = $request->query('hub_challenge');

        $expected = config('services.whatsapp.verify_token');

        if ($mode === 'subscribe' && $token && hash_equals((string) $expected, (string) $token)) {
            // Plain text response with the challenge (Meta needs exactly this)
            return response($challenge, 200)->header('Content-Type', 'text/plain');
        }

        Log::warning('WhatsApp webhook verify failed', [
            'mode'  => $mode,
            'token' => $token ? '***' : null,
        ]);
        return response('Forbidden', 403);
    }

    /**
     * POST /webhooks/whatsapp — every incoming message + status update arrives here.
     *
     * Meta retries failed webhooks aggressively, so:
     *   - Always return 200 OK (even on internal errors — we log them).
     *   - Be idempotent: skip duplicate provider_message_id.
     */
    public function receive(Request $request)
    {
        // 1) Signature verification
        if (!$this->verifySignature($request)) {
            Log::warning('WhatsApp webhook signature mismatch', ['ip' => $request->ip()]);
            return response('Invalid signature', 403);
        }

        $payload = $request->all();

        // Meta wraps everything under entry[].changes[].value
        foreach (data_get($payload, 'entry', []) as $entry) {
            foreach (data_get($entry, 'changes', []) as $change) {
                if (data_get($change, 'field') !== 'messages') continue;

                $value = data_get($change, 'value', []);

                // a) Incoming messages
                foreach (data_get($value, 'messages', []) as $msg) {
                    $this->handleIncomingMessage($msg, $value);
                }

                // b) Status updates for messages we sent (sent → delivered → read)
                foreach (data_get($value, 'statuses', []) as $st) {
                    $this->handleStatusUpdate($st);
                }
            }
        }

        return response()->json(['ok' => true]);
    }

    protected function verifySignature(Request $request): bool
    {
        $appSecret = config('services.whatsapp.app_secret');
        // If you haven't set APP_SECRET yet, skip verification (logs a warning so you don't miss it)
        if (!$appSecret || str_starts_with((string) $appSecret, 'REPLACE_')) {
            Log::warning('WhatsApp webhook signature skipped — set WHATSAPP_APP_SECRET in .env to enable');
            return true;
        }

        $signatureHeader = $request->header('X-Hub-Signature-256');
        if (!$signatureHeader || !str_starts_with($signatureHeader, 'sha256=')) {
            return false;
        }
        $provided = substr($signatureHeader, 7);
        $expected = hash_hmac('sha256', $request->getContent(), $appSecret);

        return hash_equals($expected, $provided);
    }

    protected function handleIncomingMessage(array $msg, array $value): void
    {
        $providerId = data_get($msg, 'id');
        if (!$providerId) return;

        // Idempotency — don't double-insert if Meta retries
        if (Communication::where('provider_message_id', $providerId)->exists()) return;

        $from   = data_get($msg, 'from');             // customer's number (digits, no +)
        $type   = data_get($msg, 'type', 'text');
        $body   = $this->extractBody($msg, $type);
        $toPnId = data_get($value, 'metadata.phone_number_id');

        // Match this number to one of our leads (try several phone forms)
        $lead = $this->findLeadByPhone($from);
        if (!$lead) {
            Log::info('WhatsApp message received from unknown number', ['from' => $from, 'preview' => mb_substr($body ?? '', 0, 60)]);
            return; // Nothing to attach to — drop quietly
        }

        Communication::create([
            'lead_id'             => $lead->id,
            'user_id'             => null, // it's the customer talking, not a CRM user
            'channel'             => 'whatsapp',
            'direction'           => 'in',
            'from_address'        => $from,
            'to_address'          => $toPnId,
            'body'                => $body ?: '[' . $type . ']',
            'status'              => 'delivered',
            'provider_message_id' => $providerId,
        ]);

        Activity::log([
            'lead_id'     => $lead->id,
            'user_id'     => null,
            'action'      => 'whatsapp_received',
            'description' => 'Lead replied on WhatsApp',
        ]);
    }

    protected function handleStatusUpdate(array $st): void
    {
        $providerId = data_get($st, 'id');
        $status     = data_get($st, 'status'); // sent, delivered, read, failed

        if (!$providerId || !$status) return;

        $valid = ['sent', 'delivered', 'read', 'failed'];
        if (!in_array($status, $valid, true)) return;

        Communication::where('provider_message_id', $providerId)
            ->whereIn('status', ['queued', 'sent', 'delivered'])
            ->update(['status' => $status]);
    }

    protected function extractBody(array $msg, string $type): ?string
    {
        return match ($type) {
            'text'        => data_get($msg, 'text.body'),
            'button'      => data_get($msg, 'button.text'),
            'interactive' => data_get($msg, 'interactive.button_reply.title')
                          ?? data_get($msg, 'interactive.list_reply.title'),
            'image'       => '[image] ' . (data_get($msg, 'image.caption') ?? ''),
            'video'       => '[video] ' . (data_get($msg, 'video.caption') ?? ''),
            'audio'       => '[audio message]',
            'document'    => '[document: ' . (data_get($msg, 'document.filename') ?? '') . ']',
            'location'    => '[location: ' . data_get($msg, 'location.latitude') . ',' . data_get($msg, 'location.longitude') . ']',
            'contacts'    => '[shared contact]',
            'sticker'     => '[sticker]',
            default       => '[' . $type . ']',
        };
    }

    protected function findLeadByPhone(?string $digits): ?Lead
    {
        if (!$digits) return null;

        // Normalize the incoming Meta number ('919876543210') against multiple lead forms:
        // 1. Exact match on phone
        $exact = Lead::where('phone', $digits)
            ->orWhere('whatsapp', $digits)
            ->orWhere('alternate_phone', $digits)
            ->first();
        if ($exact) return $exact;

        // 2. Strip default country code prefix and try last 10 digits
        $cc = (string) config('services.whatsapp.default_country_code', '91');
        $local = $digits;
        if (str_starts_with($digits, $cc) && strlen($digits) > 10) {
            $local = substr($digits, strlen($cc));
        } elseif (strlen($digits) > 10) {
            $local = substr($digits, -10);
        }

        return Lead::where('phone', 'LIKE', '%' . $local)
            ->orWhere('whatsapp', 'LIKE', '%' . $local)
            ->orWhere('alternate_phone', 'LIKE', '%' . $local)
            ->first();
    }
}
