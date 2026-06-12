<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Activity extends Model
{
    protected $guarded = ['id'];

    protected $casts = [
        'meta' => 'array',
    ];

    public function lead(): BelongsTo
    {
        return $this->belongsTo(Lead::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function fromUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'from_user_id');
    }

    public function toUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'to_user_id');
    }

    public static function log(array $data): self
    {
        if (!array_key_exists('user_id', $data) && auth()->check()) {
            $data['user_id'] = auth()->id();
        }
        return self::create($data);
    }

    public function getIconAttribute(): string
    {
        return match ($this->action) {
            'lead_created'     => 'plus-circle',
            'lead_updated'     => 'pencil-square',
            'assigned'         => 'user-plus',
            'reassigned'       => 'arrow-path',
            'status_changed'   => 'flag',
            'comment_added'    => 'chat-bubble',
            'followup_added'   => 'calendar',
            'followup_done'    => 'check-circle',
            'converted'        => 'trophy',
            'lost'             => 'x-circle',
            'bulk_imported'    => 'arrow-up-tray',
            default            => 'bell',
        };
    }

    public function getColorAttribute(): string
    {
        return match ($this->action) {
            'reassigned'    => 'text-amber-600 bg-amber-100',
            'assigned'      => 'text-emerald-600 bg-emerald-100',
            'converted'     => 'text-green-700 bg-green-100',
            'lost'          => 'text-rose-600 bg-rose-100',
            'status_changed'=> 'text-indigo-600 bg-indigo-100',
            'comment_added' => 'text-blue-600 bg-blue-100',
            'lead_created'  => 'text-teal-600 bg-teal-100',
            'lead_updated'  => 'text-purple-600 bg-purple-100',
            default         => 'text-gray-600 bg-gray-100',
        };
    }
}
