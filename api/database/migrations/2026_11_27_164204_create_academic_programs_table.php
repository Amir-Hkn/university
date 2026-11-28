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
        Schema::create('academic_programs', function (Blueprint $table) {
            $table->id();
            $table->string('code', 64)->unique();
            $table->string('name', 150);
            $table->string('degree_level', 32);
            $table->timestamps();
        });

        DB::statement("
            ALTER TABLE academic_programs
            ADD CONSTRAINT academic_programs_code_check
            CHECK (btrim(code) <> '')
        ");

        DB::statement("
            ALTER TABLE academic_programs
            ADD CONSTRAINT academic_programs_name_check
            CHECK (btrim(name) <> '')
        ");

        DB::statement("
            ALTER TABLE academic_programs
            ADD CONSTRAINT academic_programs_degree_level_check
            CHECK (btrim(degree_level) <> '')
        ");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('academic_programs');
    }
};
