<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement("ALTER TABLE `leads` MODIFY `status` ENUM(
            'new','contacted','interested','follow_up','qualified','demo_scheduled',
            'proposal_sent','negotiation','converted','lost','junk',
            'good_lead','fake_lead','in_conversation'
        ) NOT NULL DEFAULT 'new'");
    }

    public function down(): void
    {
        DB::statement("ALTER TABLE `leads` MODIFY `status` ENUM(
            'new','contacted','interested','follow_up','qualified','demo_scheduled',
            'proposal_sent','negotiation','converted','lost','junk'
        ) NOT NULL DEFAULT 'new'");
    }
};
