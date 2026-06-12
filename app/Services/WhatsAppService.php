<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class WhatsAppService
{
    public function isConfigured(): bool
    {
        $cfg = config('services.whatsapp');
        return !empty($cfg['phone_number_id'])
            && !empty($cfg['access_token'])
            && !str_starts_with((string) $cfg['access_token'], 'REPLACE_');
    }

    /**
     * Send a plain text message (only allowed inside the 24-hour customer service window).
     * For first-touch outreach, use sendTemplate() with an approved template.
     *
     * @return array{ok:bool, message_id?:string, error?:string, raw?:array}
     */
    public function sendText(string $phone, string $body): array
    {
        $to = $this->normalize($phone);
        if (!$to) {
            return ['ok' => false, 'error' => 'Invalid phone number.'];
        }

        return $this->call([
            'messaging_product' => 'whatsapp',
            'to'                => $to,
            'type'              => 'text',
            'text'              => ['preview_url' => true, 'body' => $body],
        ]);
    }

    /**
     * Send an approved template message (required for first-touch / outside 24hr window).
     */
    public function sendTemplate(string $phone, string $templateName, string $languageCode = 'en_US', array $bodyParams = []): array
    {
        $to = $this->normalize($phone);
        if (!$to) {
            return ['ok' => false, 'error' => 'Invalid phone number.'];
        }

        $payload = [
            'messaging_product' => 'whatsapp',
            'to'                => $to,
            'type'              => 'template',
            'template'          => [
                'name'     => $templateName,
                'language' => ['code' => $languageCode],
            ],
        ];

        if (!empty($bodyParams)) {
            $payload['template']['components'] = [[
                'type'       => 'body',
                'parameters' => array_map(fn ($p) => ['type' => 'text', 'text' => (string) $p], $bodyParams),
            ]];
        }

        return $this->call($payload);
    }

    public function normalize(?string $phone): ?string
    {
        if (!$phone) return null;
        // Strip all non-digits
        $digits = preg_replace('/\D+/', '', $phone);
        if ($digits === '') return null;

        // If number doesn't already include a country code (looks like 10 digits), prepend default
        $cc = (string) config('services.whatsapp.default_country_code', '91');
        if (strlen($digits) === 10) {
            $digits = $cc . $digits;
        }
        return $digits;
    }

    protected function call(array $payload): array
    {
        if (!$this->isConfigured()) {
            return ['ok' => false, 'error' => 'WhatsApp is not configured. Add WHATSAPP_PHONE_NUMBER_ID + WHATSAPP_ACCESS_TOKEN in .env.'];
        }

        $cfg = config('services.whatsapp');
        $url = sprintf('https://graph.facebook.com/%s/%s/messages', $cfg['api_version'], $cfg['phone_number_id']);

        try {
            $res = Http::withToken($cfg['access_token'])
                ->acceptJson()
                ->asJson()
                ->timeout(15)
                ->post($url, $payload);

            $body = $res->json();
            if ($res->successful()) {
                $id = data_get($body, 'messages.0.id');
                return ['ok' => true, 'message_id' => $id, 'raw' => $body];
            }
            $err = data_get($body, 'error.message') ?: ('HTTP ' . $res->status());
            Log::warning('WhatsApp send failed', ['url' => $url, 'payload' => $payload, 'response' => $body]);
            return ['ok' => false, 'error' => $err, 'raw' => $body];
        } catch (\Throwable $e) {
            Log::error('WhatsApp exception', ['msg' => $e->getMessage()]);
            return ['ok' => false, 'error' => $e->getMessage()];
        }
    }
}
