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
        Schema::create('staff_permission_grants', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')
                ->constrained('users')
                ->restrictOnDelete();

            $table->foreignId('permission_id')
                ->constrained('permissions')
                ->restrictOnDelete();

            $table->foreignId('staff_program_scope_id')
                ->nullable();

            $table->foreign(
                ['staff_program_scope_id', 'user_id'],
                'staff_grants_scope_owner_fk'
            )
                ->references(['id', 'user_id'])
                ->on('staff_program_scopes')
                ->restrictOnDelete();

            $table->index('permission_id');

            $table->index(
                ['staff_program_scope_id', 'user_id'],
                'staff_grants_scope_owner_index'
            );
            $table->timestamps();
        });

        DB::statement("
            ALTER TABLE staff_permission_grants
            ADD CONSTRAINT staff_permission_grants_unique
            UNIQUE NULLS NOT DISTINCT (
                user_id,
                permission_id,
                staff_program_scope_id
            )
        ");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('staff_permission_grants');
    }
};
