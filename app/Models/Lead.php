<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Lead extends Model
{
    use HasFactory, SoftDeletes;

    protected $guarded = ['id'];

    protected $casts = [
        'date_of_birth'        => 'date',
        'next_followup_date'   => 'date',
        'last_contacted_at'    => 'datetime',
        'assigned_at'          => 'datetime',
        'converted_at'         => 'datetime',
        'status_changed_at'    => 'datetime',
        'tags'                 => 'array',
        'custom_fields'        => 'array',
        'experience_years'     => 'decimal:1',
        'budget'               => 'decimal:2',
        'conversion_amount'    => 'decimal:2',
    ];

    public const STATUSES = [
        'new' => 'New',
        'contacted' => 'Contacted',
        'interested' => 'Interested',
        'good_lead' => 'Good Lead',
        'in_conversation' => 'In Conversation',
        'follow_up' => 'Follow Up',
        'qualified' => 'Qualified',
        'demo_scheduled' => 'Demo Scheduled',
        'proposal_sent' => 'Proposal Sent',
        'negotiation' => 'Negotiation',
        'converted' => 'Converted',
        'lost' => 'Lost',
        'fake_lead' => 'Fake Lead',
        'junk' => 'Junk',
    ];

    public const SOURCES = [
        'website', 'facebook', 'instagram', 'google_ads', 'youtube', 'linkedin',
        'whatsapp', 'referral', 'walkin', 'cold_call', 'email', 'newspaper', 'event', 'other',
    ];

    public const COURSES = [
        'MBA', 'BBA', 'BCA', 'MCA', 'B.Tech', 'M.Tech', 'B.Com', 'M.Com',
        'Digital Marketing', 'Data Science', 'Full Stack Development',
        'UI/UX Design', 'Graphic Design', 'Cyber Security', 'Cloud Computing', 'Other',
    ];

    public function assignedTo(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    public function assignedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_by');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function comments(): HasMany
    {
        return $this->hasMany(LeadComment::class)->latest();
    }

    public function followups(): HasMany
    {
        return $this->hasMany(LeadFollowup::class)->latest();
    }

    public function activities(): HasMany
    {
        return $this->hasMany(Activity::class)->latest();
    }

    public function getStatusLabelAttribute(): string
    {
        return self::STATUSES[$this->status] ?? ucfirst($this->status);
    }

    public function getStatusColorAttribute(): string
    {
        return match ($this->status) {
            'new' => 'bg-blue-100 text-blue-700 border-blue-300',
            'contacted' => 'bg-indigo-100 text-indigo-700 border-indigo-300',
            'interested' => 'bg-purple-100 text-purple-700 border-purple-300',
            'good_lead' => 'bg-emerald-50 text-emerald-700 border-emerald-300',
            'in_conversation' => 'bg-sky-100 text-sky-700 border-sky-300',
            'follow_up' => 'bg-amber-100 text-amber-800 border-amber-300',
            'qualified' => 'bg-teal-100 text-teal-700 border-teal-300',
            'demo_scheduled' => 'bg-cyan-100 text-cyan-700 border-cyan-300',
            'proposal_sent' => 'bg-pink-100 text-pink-700 border-pink-300',
            'negotiation' => 'bg-orange-100 text-orange-700 border-orange-300',
            'converted' => 'bg-emerald-100 text-emerald-700 border-emerald-300',
            'lost' => 'bg-rose-100 text-rose-700 border-rose-300',
            'fake_lead' => 'bg-red-200 text-red-800 border-red-400',
            'junk' => 'bg-gray-200 text-gray-700 border-gray-300',
            default => 'bg-gray-100 text-gray-700 border-gray-300',
        };
    }

    public function statusChangedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'status_changed_by');
    }

    public function communications(): HasMany
    {
        return $this->hasMany(Communication::class);
    }
}
