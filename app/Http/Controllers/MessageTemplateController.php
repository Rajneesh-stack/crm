<?php

namespace App\Http\Controllers;

use App\Models\MessageTemplate;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class MessageTemplateController extends Controller
{
    public function index()
    {
        $whatsapp = MessageTemplate::channel('whatsapp')->orderBy('sort_order')->orderBy('label')->get();
        $email    = MessageTemplate::channel('email')->orderBy('sort_order')->orderBy('label')->get();
        return view('templates.index', compact('whatsapp', 'email'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'channel'        => ['required', 'in:whatsapp,email'],
            'label'          => ['required', 'string', 'max:191'],
            'subject'        => ['nullable', 'string', 'max:255'],
            'body'           => ['required', 'string', 'max:20000'],
            'is_active'      => ['nullable', 'boolean'],
            'attachments.*'  => ['nullable', 'file', 'max:10240'], // 10 MB per file
        ]);

        // Auto-generate a unique key from the label
        $key  = Str::slug($data['label'], '_');
        if (!$key) $key = 'tpl_' . substr(md5($data['label']), 0, 8);
        $base = $key;
        $i = 1;
        while (MessageTemplate::where('channel', $data['channel'])->where('key', $key)->exists()) {
            $key = $base . '_' . (++$i);
        }

        // Attachments are only meaningful for email templates
        $storedPaths = [];
        if ($data['channel'] === 'email' && $request->hasFile('attachments')) {
            foreach ($request->file('attachments') as $file) {
                $storedPaths[] = $file->store('template-attachments', 'public');
            }
        }

        MessageTemplate::create([
            'channel'    => $data['channel'],
            'key'        => $key,
            'label'      => $data['label'],
            'subject'    => $data['channel'] === 'email' ? ($data['subject'] ?? '') : null,
            'body'       => $data['body'],
            'attachments'=> $storedPaths ?: null,
            'is_active'  => $request->boolean('is_active', true),
            'sort_order' => MessageTemplate::channel($data['channel'])->max('sort_order') + 1,
        ]);

        return back()->with('success', 'Template added.');
    }

    public function update(Request $request, MessageTemplate $template)
    {
        $data = $request->validate([
            'label'           => ['required', 'string', 'max:191'],
            'subject'         => ['nullable', 'string', 'max:255'],
            'body'            => ['required', 'string', 'max:20000'],
            'is_active'       => ['nullable', 'boolean'],
            'sort_order'      => ['nullable', 'integer', 'min:0'],
            'attachments.*'   => ['nullable', 'file', 'max:10240'],
            'remove_attachment' => ['nullable', 'array'],
            'remove_attachment.*' => ['string'],
        ]);

        $existing = $template->attachments ?? [];

        // 1) Remove attachments the user explicitly unchecked
        if ($removeList = $request->input('remove_attachment')) {
            foreach ($removeList as $relPath) {
                if (in_array($relPath, $existing, true)) {
                    Storage::disk('public')->delete($relPath);
                    $existing = array_values(array_filter($existing, fn ($p) => $p !== $relPath));
                }
            }
        }

        // 2) Add any newly uploaded files (email only)
        if ($template->channel === 'email' && $request->hasFile('attachments')) {
            foreach ($request->file('attachments') as $file) {
                $existing[] = $file->store('template-attachments', 'public');
            }
        }

        $template->fill([
            'label'      => $data['label'],
            'subject'    => $template->channel === 'email' ? ($data['subject'] ?? '') : null,
            'body'       => $data['body'],
            'attachments'=> $existing ?: null,
            'is_active'  => $request->boolean('is_active'),
            'sort_order' => $data['sort_order'] ?? $template->sort_order,
        ])->save();

        return back()->with('success', 'Template updated.');
    }

    public function destroy(MessageTemplate $template)
    {
        // Clean up attachment files on disk too
        foreach (($template->attachments ?? []) as $relPath) {
            Storage::disk('public')->delete($relPath);
        }
        $template->delete();
        return back()->with('success', 'Template deleted.');
    }
}
