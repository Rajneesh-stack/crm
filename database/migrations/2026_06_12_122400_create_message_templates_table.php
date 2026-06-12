<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('message_templates', function (Blueprint $table) {
            $table->id();
            $table->enum('channel', ['whatsapp', 'email'])->index();
            $table->string('key', 100);            // slug, unique per channel
            $table->string('label', 191);           // display name
            $table->string('subject', 255)->nullable(); // email only
            $table->text('body');
            $table->boolean('is_active')->default(true);
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();

            $table->unique(['channel', 'key']);
        });

        // Seed defaults from the previous config arrays
        $now = now();
        $whatsapp = [
            ['key' => 'greeting',  'label' => 'Greeting',                'body' => "Hello {name}, thanks for showing interest in our {course} program. I'll be your point of contact going forward. Feel free to reply here with any questions!"],
            ['key' => 'callback',  'label' => 'Missed call follow-up',   'body' => "Hi {name}, I just tried calling you about the {course} program but couldn't reach you. When would be a good time for a quick chat?"],
            ['key' => 'fee_info',  'label' => 'Fee information',         'body' => "Hi {name}, here are the details for the {course} program. I'll share the full brochure shortly. Let me know what works best for you."],
            ['key' => 'reminder',  'label' => 'Reminder',                'body' => "Hi {name}, just a quick reminder about our discussion on the {course} program. Let me know if you'd like to proceed or have any questions."],
            ['key' => 'thanks',    'label' => 'Thank you',               'body' => "Thank you {name}! It was great talking to you. I'll follow up shortly with the next steps."],
        ];
        foreach ($whatsapp as $i => $t) {
            DB::table('message_templates')->insert([
                'channel'   => 'whatsapp',
                'key'       => $t['key'],
                'label'     => $t['label'],
                'subject'   => null,
                'body'      => $t['body'],
                'is_active' => true,
                'sort_order'=> $i,
                'created_at'=> $now,
                'updated_at'=> $now,
            ]);
        }

        $email = [
            ['key' => 'introduction', 'label' => 'Introduction', 'subject' => 'Following up on your interest in {course}',
             'body' => "Hi {name},\n\nThanks for showing interest in our {course} program. I'm {counselor} and I'll be guiding you through the next steps.\n\nWhen would be a convenient time for a 15-minute call?\n\nWarm regards,\n{counselor}"],
            ['key' => 'brochure', 'label' => 'Brochure share', 'subject' => 'Details: {course} program',
             'body' => "Hi {name},\n\nAs discussed, please find the details for the {course} program. Do reply if you have any questions.\n\nRegards,\n{counselor}"],
        ];
        foreach ($email as $i => $t) {
            DB::table('message_templates')->insert([
                'channel'   => 'email',
                'key'       => $t['key'],
                'label'     => $t['label'],
                'subject'   => $t['subject'],
                'body'      => $t['body'],
                'is_active' => true,
                'sort_order'=> $i,
                'created_at'=> $now,
                'updated_at'=> $now,
            ]);
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('message_templates');
    }
};
