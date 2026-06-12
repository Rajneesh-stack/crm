<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('lead_comments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('lead_id')->constrained('leads')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->text('comment');
            $table->enum('outcome', [
                'call_not_picked','call_back_later','interested','not_interested',
                'wrong_number','switched_off','busy','already_enrolled','demo_done',
                'email_sent','whatsapp_sent','visited','other'
            ])->nullable();
            $table->date('next_followup_date')->nullable();
            $table->time('next_followup_time')->nullable();
            $table->timestamps();

            $table->index('lead_id');
            $table->index('user_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('lead_comments');
    }
};
