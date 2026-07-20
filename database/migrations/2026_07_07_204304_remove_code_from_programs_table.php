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
        Schema::table('programs', function (Blueprint $table) {
            $table->dropUnique('programs_code_unique');
            $table->dropColumn('code');
            $table->unique('short_name');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('programs', function (Blueprint $table) {
            // Re-adding the column. It's nullable because existing rows won't have a value.
            // A default value would be needed for non-nullable unique columns on existing data.
            // For a fresh DB, this is less of an issue.
            $table->string('code')->unique()->nullable()->after('id');
            $table->dropUnique('programs_short_name_unique');
        });
    }
};
