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
        Schema::create('staff_program_scopes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')
                ->constrained('users')
                ->restrictOnDelete();

            $table->foreignId('program_id')
                ->constrained('academic_programs')
                ->restrictOnDelete();

            $table->unique(
                ['user_id', 'program_id'],
                'staff_scopes_user_program_unique'
            );

            $table->unique(
                ['id', 'user_id'],
                'staff_scopes_id_user_unique'
            );

            $table->index('program_id');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('staff_program_scopes');
    }
};
