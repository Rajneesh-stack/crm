<?php

namespace App\Http\Controllers;

use App\Models\Activity;
use App\Models\Communication;
use App\Models\Lead;
use App\Services\WhatsAppService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;

class CommunicationController extends Controller
{
    public function sendWhatsapp(Request $request, Lead $lead, WhatsAppService $wa)
    {
        $this->authorizeLead($lead);

        $data = $request->validate([
            'body'         => ['required_without:template_name', 'string', 'max:4096'],
            'template_name'=> ['nullable', 'string', 'max:191'],
            'language'     => ['nullable', 'string', 'max:10'],
            'template_key' => ['nullable', 'string', 'max:191'],
            'to_address'   => ['nullable', 'string', 'max:30'],
        ]);

        $user  = auth()->user();
        $phone = $data['to_address'] ?? $lead->phone;
        if (!$phone) {
            return back()->with('error', 'Please enter a phone number to send to.');
        }

        $body = $this->fillPlaceholders($data['body'] ?? '', $lead, $user);

        $comm = Communication::create([
            'lead_id'      => $lead->id,
            'user_id'      => $user->id,
            'channel'      => 'whatsapp',
            'direction'    => 'out',
            'to_address'   => $phone,
            'body'         => $body ?: ('[Template: '.($data['template_name'] ?? '-').']'),
            'template_key' => $data['template_key'] ?? null,
            'status'       => 'queued',
        ]);

        if (!empty($data['template_name'])) {
            $result = $wa->sendTemplate($phone, $data['template_name'], $data['language'] ?? 'en_US');
        } else {
            $result = $wa->sendText($phone, $body);
        }

        if ($result['ok']) {
            $comm->update([
                'status'              => 'sent',
                'provider_message_id' => $result['message_id'] ?? null,
            ]);
            Activity::log([
                'lead_id'     => $lead->id,
                'user_id'     => $user->id,
                'action'      => 'whatsapp_sent',
                'description' => "WhatsApp message sent",
            ]);
            return back()->with('success', 'WhatsApp message sent.');
        }

        $comm->update(['status' => 'failed', 'error' => $result['error'] ?? 'Unknown']);
        return back()->with('error', 'WhatsApp send failed: ' . ($result['error'] ?? 'Unknown'));
    }

    public function sendEmail(Request $request, Lead $lead)
    {
        $this->authorizeLead($lead);

        $data = $request->validate([
            'subject'        => ['required', 'string', 'max:255'],
            'body'           => ['required', 'string', 'max:20000'],
            'template_key'   => ['nullable', 'string', 'max:191'],
            'to_address'     => ['nullable', 'email', 'max:191'],
            'attachments.*'  => ['nullable', 'file', 'max:10240'], // 10 MB each
        ]);

        $user = auth()->user();
        $toEmail = $data['to_address'] ?? $lead->email;
        if (!$toEmail) {
            return back()->with('error', 'Please enter a recipient email address.');
        }

        $subject = $this->fillPlaceholders($data['subject'], $lead, $user);
        $body    = $this->fillPlaceholders($data['body'],    $lead, $user);

        // ------ Collect attachments ------
        // 1) Files attached to the selected template (admin-managed)
        $templateAttachmentPaths = [];
        if (!empty($data['template_key'])) {
            $tpl = \App\Models\MessageTemplate::channel('email')
                ->where('key', $data['template_key'])->first();
            if ($tpl && !empty($tpl->attachments)) {
                foreach ($tpl->attachments as $rel) {
                    $abs = storage_path('app/public/' . $rel);
                    if (is_file($abs)) $templateAttachmentPaths[] = $abs;
                }
            }
        }
        // 2) Ad-hoc files uploaded with this specific send (stored temporarily for the request)
        $adhocAttachments = [];
        if ($request->hasFile('attachments')) {
            foreach ($request->file('attachments') as $f) {
                $adhocAttachments[] = ['path' => $f->getRealPath(), 'name' => $f->getClientOriginalName()];
            }
        }

        $comm = Communication::create([
            'lead_id'      => $lead->id,
            'user_id'      => $user->id,
            'channel'      => 'email',
            'direction'    => 'out',
            'to_address'   => $toEmail,
            'from_address' => config('mail.from.address'),
            'subject'      => $subject,
            'body'         => $body,
            'template_key' => $data['template_key'] ?? null,
            'status'       => 'queued',
        ]);

        try {
            Mail::raw($body, function ($m) use ($toEmail, $lead, $subject, $user, $templateAttachmentPaths, $adhocAttachments) {
                $m->to($toEmail, $lead->name)->subject($subject);
                if ($user && $user->email) {
                    $m->replyTo($user->email, $user->name);
                }
                foreach ($templateAttachmentPaths as $abs) {
                    $m->attach($abs);
                }
                foreach ($adhocAttachments as $a) {
                    $m->attach($a['path'], ['as' => $a['name']]);
                }
            });
            $comm->update(['status' => 'sent']);
            $attCount = count($templateAttachmentPaths) + count($adhocAttachments);
            Activity::log([
                'lead_id'     => $lead->id,
                'user_id'     => $user->id,
                'action'      => 'email_sent',
                'description' => "Email sent: ".$subject . ($attCount ? " (with $attCount attachment".($attCount > 1 ? 's' : '').")" : ''),
            ]);
            return back()->with('success', 'Email sent' . ($attCount ? " with $attCount attachment".($attCount > 1 ? 's' : '') : '') . '.');
        } catch (\Throwable $e) {
            $comm->update(['status' => 'failed', 'error' => $e->getMessage()]);
            return back()->with('error', 'Email send failed: ' . $e->getMessage());
        }
    }

    protected function authorizeLead(Lead $lead): void
    {
        $user = auth()->user();
        if (!$user) abort(403);
        if (!$user->isAdmin() && $lead->assigned_to !== $user->id) {
            abort(403, 'This lead is not assigned to you.');
        }
    }

    protected function fillPlaceholders(string $text, Lead $lead, $user): string
    {
        $map = [
            '{name}'      => $lead->name,
            '{course}'    => $lead->course ?: 'our program',
            '{phone}'     => $lead->phone,
            '{email}'     => $lead->email,
            '{counselor}' => $user?->name ?: 'the team',
        ];
        return strtr($text, $map);
    }
}
