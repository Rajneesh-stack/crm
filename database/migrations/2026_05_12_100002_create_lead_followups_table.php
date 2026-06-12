<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('lead_followups', function (Blueprint $table) {
            $table->id();
            $table->foreignId('lead_id')->constrained('leads')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->date('scheduled_date');
            $table->time('scheduled_time')->nullable();
            $table->enum('status', ['pending', 'completed', 'overdue', 'skipped'])->default('pending');
            $table->timestamp('completed_at')->nullable();
            $table->text('remark')->nullable();
            $table->timestamps();

            $table->index(['lead_id', 'status']);
            $table->index('scheduled_date');
            $table->index('user_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('lead_followups');
    }
};
