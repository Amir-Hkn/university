<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('verification_reviews', function (Blueprint $table) {
            $table->id();

            $table->foreignId('case_id')
                ->constrained('identity_verification_cases')
                ->restrictOnDelete();

            $table->foreignId('reviewer_user_id')
                ->constrained('users')
                ->restrictOnDelete();

            $table->string('decision', 20);

            $table->text('reason')->nullable();

            $table->timestampTz('reviewed_at');

            $table->index(
                'reviewer_user_id',
                'vr_reviewer_user_index'
            );
        });

        DB::statement(<<<'SQL'
            ALTER TABLE verification_reviews
                ADD CONSTRAINT vr_decision_check
                    CHECK (
                        decision IN (
                            'approved',
                            'rejected'
                        )
                    ),

                ADD CONSTRAINT vr_reason_check
                    CHECK (
                        reason IS NULL
                        OR reason ~ '[^[:space:]]'
                    ),

                ADD CONSTRAINT vr_rejection_reason_check
                    CHECK (
                        decision <> 'rejected'
                        OR reason IS NOT NULL
                    )
        SQL);

        DB::statement(<<<'SQL'
            CREATE INDEX vr_case_reviewed_at_index
            ON verification_reviews (
                case_id,
                reviewed_at DESC
            )
        SQL);
    }

    public function down(): void
    {
        Schema::dropIfExists('verification_reviews');
    }
};
