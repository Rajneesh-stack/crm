<?php

namespace App\Console\Commands;

use App\Models\LeadFollowup;
use Illuminate\Console\Command;

class MarkOverdueFollowups extends Command
{
    protected $signature = 'followups:mark-overdue';
    protected $description = 'Mark pending follow-ups whose scheduled date is past as overdue';

    public function handle(): int
    {
        $count = LeadFollowup::where('status', 'pending')
            ->whereDate('scheduled_date', '<', now()->toDateString())
            ->update(['status' => 'overdue']);

        $this->info("Marked $count follow-ups overdue.");
        return self::SUCCESS;
    }
}
