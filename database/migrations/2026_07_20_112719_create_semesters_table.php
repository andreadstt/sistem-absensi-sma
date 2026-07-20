<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('semesters', function (Blueprint $table) {
            $table->id();
            $table->foreignId('academic_year_id')->constrained()->cascadeOnDelete();
            $table->enum('type', ['1', '2'])->comment('1 for Ganjil, 2 for Genap');
            $table->date('start_date');
            $table->date('end_date');
            $table->timestamps();
            
            $table->unique(['academic_year_id', 'type']);
        });

        // Data Backfill for existing academic years
        $academicYears = DB::table('academic_years')->get();
        foreach ($academicYears as $year) {
            DB::table('semesters')->insert([
                [
                    'academic_year_id' => $year->id,
                    'type' => '1',
                    'start_date' => $year->start_year . '-07-01',
                    'end_date' => $year->start_year . '-12-31',
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
                [
                    'academic_year_id' => $year->id,
                    'type' => '2',
                    'start_date' => $year->end_year . '-01-01',
                    'end_date' => $year->end_year . '-06-30',
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
            ]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('semesters');
    }
};
