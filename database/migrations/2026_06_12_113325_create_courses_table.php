<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('courses', function (Blueprint $table) {
            $table->id();
            $table->string('name', 191)->unique();
            $table->boolean('is_active')->default(true);
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
        });

        // Seed defaults from the previous hardcoded list
        $defaults = ['MBA','BBA','BCA','MCA','B.Tech','M.Tech','B.Com','M.Com',
            'Digital Marketing','Data Science','Full Stack Development',
            'UI/UX Design','Graphic Design','Cyber Security','Cloud Computing','Other'];
        foreach ($defaults as $i => $name) {
            \DB::table('courses')->insert([
                'name'       => $name,
                'is_active'  => true,
                'sort_order' => $i,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('courses');
    }
};
