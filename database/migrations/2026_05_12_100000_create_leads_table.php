<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('leads', function (Blueprint $table) {
            $table->id();

            // Mandatory
            $table->string('name');
            $table->string('phone', 30);

            // Contact (all nullable)
            $table->string('alternate_phone', 30)->nullable();
            $table->string('email')->nullable();
            $table->string('whatsapp', 30)->nullable();

            // Course / interest
            $table->string('course')->nullable();
            $table->string('sub_course')->nullable();
            $table->string('mode')->nullable(); // online / offline / hybrid
            $table->string('preferred_batch')->nullable();
            $table->decimal('budget', 12, 2)->nullable();

            // Source
            $table->string('source')->nullable(); // website, facebook, google, referral, walkin
            $table->string('sub_source')->nullable();
            $table->string('campaign')->nullable();
            $table->string('utm_source')->nullable();
            $table->string('utm_medium')->nullable();
            $table->string('utm_campaign')->nullable();
            $table->string('referrer_name')->nullable();
            $table->string('referrer_phone', 30)->nullable();

            // Personal
            $table->date('date_of_birth')->nullable();
            $table->enum('gender', ['male', 'female', 'other'])->nullable();
            $table->string('occupation')->nullable();
            $table->string('qualification')->nullable();
            $table->string('passing_year')->nullable();
            $table->string('institute')->nullable();
            $table->string('company')->nullable();
            $table->string('designation')->nullable();
            $table->decimal('experience_years', 5, 1)->nullable();

            // Address
            $table->text('address')->nullable();
            $table->string('city')->nullable();
            $table->string('state')->nullable();
            $table->string('country')->nullable()->default('India');
            $table->string('pincode', 20)->nullable();

            // CRM Pipeline
            $table->enum('status', ['new', 'contacted', 'interested', 'follow_up', 'qualified', 'demo_scheduled', 'proposal_sent', 'negotiation', 'converted', 'lost', 'junk'])->default('new');
            $table->enum('priority', ['low', 'medium', 'high'])->default('medium');
            $table->unsignedTinyInteger('lead_score')->default(0);
            $table->date('next_followup_date')->nullable();
            $table->time('next_followup_time')->nullable();
            $table->timestamp('last_contacted_at')->nullable();
            $table->unsignedInteger('followup_count')->default(0);

            // Assignment
            $table->foreignId('assigned_to')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('assigned_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('assigned_at')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();

            // Conversion
            $table->timestamp('converted_at')->nullable();
            $table->decimal('conversion_amount', 12, 2)->nullable();
            $table->string('lost_reason')->nullable();

            // Misc
            $table->text('message')->nullable();
            $table->text('notes')->nullable();
            $table->json('tags')->nullable();
            $table->json('custom_fields')->nullable();

            $table->timestamps();
            $table->softDeletes();

            $table->index('phone');
            $table->index('email');
            $table->index('status');
            $table->index('next_followup_date');
            $table->index('assigned_to');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('leads');
    }
};
