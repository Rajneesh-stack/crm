<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('message_templates', function (Blueprint $table) {
            // JSON array of stored file paths (relative to storage/app/public)
            // e.g. ["template-attachments/brochure-abc.pdf", "template-attachments/fees-xyz.pdf"]
            $table->json('attachments')->nullable()->after('body');
        });
    }

    public function down(): void
    {
        Schema::table('message_templates', function (Blueprint $table) {
            $table->dropColumn('attachments');
        });
    }
};
