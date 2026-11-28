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
        Schema::create('identity_verification_cases', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')
                ->constrained('users')
                ->restrictOnDelete();

            $table->foreignId('requested_program_id')
                ->nullable()
                ->constrained('academic_programs')
                ->restrictOnDelete();

            $table->text('national_id_ciphertext')->nullable();

            $table->date('birth_date')->nullable();

            $table->string('submitted_mobile', 16)->nullable();

            $table->string('status', 20)->default('draft');

            $table->timestampTz('submitted_at')->nullable();
            $table->timestampTz('decided_at')->nullable();

            $table->index('user_id');

            $table->index([
                'requested_program_id',
                'status',
                'submitted_at',
            ], 'ivc_program_status_submitted_index');
            $table->timestamps();
        });

        DB::statement(<<<'SQL'
            ALTER TABLE identity_verification_cases
                ADD CONSTRAINT ivc_status_check
                    CHECK (
                        status IN (
                            'draft',
                            'submitted',
                            'approved',
                            'rejected'
                        )
                    ),

                ADD CONSTRAINT ivc_mobile_format_check
                    CHECK (
                        submitted_mobile IS NULL
                        OR submitted_mobile ~ '^\+[1-9][0-9]{1,14}$'
                    ),

                ADD CONSTRAINT ivc_national_id_check
                    CHECK (
                        national_id_ciphertext IS NULL
                        OR BTRIM(national_id_ciphertext) <> ''
                    ),

                ADD CONSTRAINT ivc_submission_data_check
                    CHECK (
                        status = 'draft'
                        OR (
                            requested_program_id IS NOT NULL
                            AND national_id_ciphertext IS NOT NULL
                            AND birth_date IS NOT NULL
                            AND submitted_mobile IS NOT NULL
                        )
                    ),

                ADD CONSTRAINT ivc_lifecycle_check
                    CHECK (
                        (
                            status = 'draft'
                            AND submitted_at IS NULL
                            AND decided_at IS NULL
                        )
                        OR (
                            status = 'submitted'
                            AND submitted_at IS NOT NULL
                            AND decided_at IS NULL
                        )
                        OR (
                            status IN ('approved', 'rejected')
                            AND submitted_at IS NOT NULL
                            AND decided_at IS NOT NULL
                            AND decided_at >= submitted_at
                        )
                    )
        SQL);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('identity_verification_cases');
    }
};
