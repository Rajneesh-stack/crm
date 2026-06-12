<?php

namespace App\Http\Controllers;

use App\Models\MessageTemplate;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

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
            'channel' => ['required', 'in:whatsapp,email'],
            'label'   => ['required', 'string', 'max:191'],
            'subject' => ['nullable', 'string', 'max:255'],
            'body'    => ['required', 'string', 'max:20000'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        // Auto-generate a unique key from the label
        $key  = Str::slug($data['label'], '_');
        if (!$key) $key = 'tpl_' . substr(md5($data['label']), 0, 8);
        $base = $key;
        $i = 1;
        while (MessageTemplate::where('channel', $data['channel'])->where('key', $key)->exists()) {
            $key = $base . '_' . (++$i);
        }

        MessageTemplate::create([
            'channel'   => $data['channel'],
            'key'       => $key,
            'label'     => $data['label'],
            'subject'   => $data['channel'] === 'email' ? ($data['subject'] ?? '') : null,
            'body'      => $data['body'],
            'is_active' => $request->boolean('is_active', true),
            'sort_order'=> MessageTemplate::channel($data['channel'])->max('sort_order') + 1,
        ]);

        return back()->with('success', 'Template added.');
    }

    public function update(Request $request, MessageTemplate $template)
    {
        $data = $request->validate([
            'label'    => ['required', 'string', 'max:191'],
            'subject'  => ['nullable', 'string', 'max:255'],
            'body'     => ['required', 'string', 'max:20000'],
            'is_active'=> ['nullable', 'boolean'],
            'sort_order'=> ['nullable', 'integer', 'min:0'],
        ]);

        $template->fill([
            'label'     => $data['label'],
            'subject'   => $template->channel === 'email' ? ($data['subject'] ?? '') : null,
            'body'      => $data['body'],
            'is_active' => $request->boolean('is_active'),
            'sort_order'=> $data['sort_order'] ?? $template->sort_order,
        ])->save();

        return back()->with('success', 'Template updated.');
    }

    public function destroy(MessageTemplate $template)
    {
        $template->delete();
        return back()->with('success', 'Template deleted.');
    }
}
