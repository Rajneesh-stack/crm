<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MessageTemplate extends Model
{
    protected $fillable = ['channel', 'key', 'label', 'subject', 'body', 'attachments', 'is_active', 'sort_order'];

    protected $casts = [
        'is_active'   => 'boolean',
        'sort_order'  => 'integer',
        'attachments' => 'array',
    ];

    public function scopeActive($q)
    {
        return $q->where('is_active', true)->orderBy('sort_order')->orderBy('label');
    }

    public function scopeChannel($q, string $channel)
    {
        return $q->where('channel', $channel);
    }
}
