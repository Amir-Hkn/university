<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // FSR-001: OTP verifier lifecycle.

        DB::statement(<<<'SQL'
            ALTER TABLE otp_challenges
                ALTER COLUMN secret_verifier DROP NOT NULL
        SQL);

        DB::statement(<<<'SQL'
            UPDATE otp_challenges
            SET secret_verifier = NULL
            WHERE consumed_at IS NOT NULL
               OR invalidated_at IS NOT NULL
        SQL);

        DB::statement(<<<'SQL'
            ALTER TABLE otp_challenges
                ADD CONSTRAINT otp_verifier_lifecycle_check
                    CHECK (
                        (
                            consumed_at IS NULL
                            AND invalidated_at IS NULL
                            AND secret_verifier IS NOT NULL
                        )
                        OR (
                            (
                                consumed_at IS NOT NULL
                                OR invalidated_at IS NOT NULL
                            )
                            AND secret_verifier IS NULL
                        )
                    )
        SQL);

        // FSR-002: Academic reference validation.

        DB::statement(<<<'SQL'
            ALTER TABLE academic_programs
                DROP CONSTRAINT academic_programs_code_check,
                DROP CONSTRAINT academic_programs_name_check,
                DROP CONSTRAINT academic_programs_degree_level_check,

                ADD CONSTRAINT academic_programs_code_check
                    CHECK (code ~ '[^[:space:]]'),

                ADD CONSTRAINT academic_programs_name_check
                    CHECK (name ~ '[^[:space:]]'),

                ADD CONSTRAINT academic_programs_degree_level_check
                    CHECK (degree_level ~ '[^[:space:]]')
        SQL);

        // FSR-002: Permission code validation.

        DB::statement(<<<'SQL'
            ALTER TABLE permissions
                DROP CONSTRAINT permissions_code_check,

                ADD CONSTRAINT permissions_code_check
                    CHECK (
                        code ~ '[^[:space:]]'
                        AND code !~ '^[[:space:]]|[[:space:]]$'
                    )
        SQL);

        // FSR-003: Case history index.

        DB::statement(<<<'SQL'
            CREATE INDEX ivc_user_created_at_index
            ON identity_verification_cases (
                user_id,
                created_at DESC
            )
        SQL);

        DB::statement(<<<'SQL'
            DROP INDEX identity_verification_cases_user_id_index
        SQL);
    }

    public function down(): void
    {
        DB::statement(<<<'SQL'
            LOCK TABLE otp_challenges IN ACCESS EXCLUSIVE MODE
        SQL);

        if (
            DB::table('otp_challenges')
                ->whereNull('secret_verifier')
                ->exists()
        ) {
            throw new \RuntimeException(
                'Cannot restore the previous OTP schema after verifier removal.'
            );
        }

        DB::statement(<<<'SQL'
            ALTER TABLE otp_challenges
                DROP CONSTRAINT otp_verifier_lifecycle_check,
                ALTER COLUMN secret_verifier SET NOT NULL
        SQL);

        DB::statement(<<<'SQL'
            ALTER TABLE academic_programs
                DROP CONSTRAINT academic_programs_code_check,
                DROP CONSTRAINT academic_programs_name_check,
                DROP CONSTRAINT academic_programs_degree_level_check,

                ADD CONSTRAINT academic_programs_code_check
                    CHECK (btrim(code) <> ''),

                ADD CONSTRAINT academic_programs_name_check
                    CHECK (btrim(name) <> ''),

                ADD CONSTRAINT academic_programs_degree_level_check
                    CHECK (btrim(degree_level) <> '')
        SQL);

        DB::statement(<<<'SQL'
            ALTER TABLE permissions
                DROP CONSTRAINT permissions_code_check,

                ADD CONSTRAINT permissions_code_check
                    CHECK (
                        btrim(code) <> ''
                        AND code = btrim(code)
                    )
        SQL);

        DB::statement(<<<'SQL'
            CREATE INDEX identity_verification_cases_user_id_index
            ON identity_verification_cases (user_id)
        SQL);

        DB::statement(<<<'SQL'
            DROP INDEX ivc_user_created_at_index
        SQL);
    }
};
