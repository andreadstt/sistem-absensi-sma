<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('programs', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique(); // "MIPA", "IPS", "BAHASA"
            $table->string('name'); // "Matematika dan Ilmu Pengetahuan Alam"
            $table->string('short_name'); // "MIPA"
            $table->text('description')->nullable();
            $table->integer('min_grade_level')->default(10); // Mulai dari kelas berapa
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('programs');
    }
};
