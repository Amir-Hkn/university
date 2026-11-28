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
        Schema::create('otp_challenges', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')
                ->nullable()
                ->constrained('users')
                ->restrictOnDelete();

            $table->string('destination', 16);
            $table->string('purpose', 20);
            $table->uuid('context_id');

            $table->string('secret_verifier', 64);

            $table->smallInteger('attempts')->default(0);

            $table->timestampTz('issued_at');
            $table->timestampTz('expires_at');

            $table->timestampTz('consumed_at')->nullable();
            $table->timestampTz('invalidated_at')->nullable();

            $table->index('user_id');

            $table->index([
                'destination',
                'purpose',
                'issued_at',
            ], 'otp_destination_purpose_issued_index');

            $table->index([
                'context_id',
                'purpose',
            ], 'otp_context_purpose_index');

            $table->index('expires_at');
            $table->timestamps();
        });

        DB::statement(<<<'SQL'
            ALTER TABLE otp_challenges
                ADD CONSTRAINT otp_purpose_check
                    CHECK (
                        purpose IN ('registration', 'login')
                    ),

                ADD CONSTRAINT otp_destination_check
                    CHECK (
                        destination ~ '^\+[1-9][0-9]{1,14}$'
                    ),

                ADD CONSTRAINT otp_secret_verifier_check
                    CHECK (
                        secret_verifier ~ '^[0-9a-f]{64}$'
                    ),

                ADD CONSTRAINT otp_attempts_check
                    CHECK (attempts >= 0),

                ADD CONSTRAINT otp_expiration_check
                    CHECK (expires_at > issued_at),

                ADD CONSTRAINT otp_login_user_check
                    CHECK (
                        purpose <> 'login'
                        OR user_id IS NOT NULL
                    ),

                ADD CONSTRAINT otp_terminal_state_check
                    CHECK (
                        consumed_at IS NULL
                        OR invalidated_at IS NULL
                    ),

                ADD CONSTRAINT otp_consumption_time_check
                    CHECK (
                        consumed_at IS NULL
                        OR (
                            consumed_at >= issued_at
                            AND consumed_at < expires_at
                        )
                    ),

                ADD CONSTRAINT otp_invalidation_time_check
                    CHECK (
                        invalidated_at IS NULL
                        OR invalidated_at >= issued_at
                    )
        SQL);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('otp_challenges');
    }
};
