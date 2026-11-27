<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (DB::table('users')->exists()) {
            throw new \RuntimeException(
                'Existing users require an explicit role backfill.'
            );
        }

        Schema::table('users', function (Blueprint $table) {
            $table->foreignId('role_id')
                ->constrained('roles')
                ->restrictOnDelete();

            $table->index('role_id');

            $table->string('mobile', 16)
                ->nullable()
                ->unique();

            $table->string('status', 20)
                ->default('active');

            $table->timestampTz('mobile_verified_at')
                ->nullable();
        });

        DB::statement("
            ALTER TABLE users
                ALTER COLUMN name DROP NOT NULL,
                ALTER COLUMN email DROP NOT NULL,
                ALTER COLUMN password DROP NOT NULL,
                ALTER COLUMN email_verified_at
                    TYPE timestamptz
                    USING email_verified_at AT TIME ZONE 'UTC',
                ALTER COLUMN created_at
                    TYPE timestamptz
                    USING created_at AT TIME ZONE 'UTC',
                ALTER COLUMN updated_at
                    TYPE timestamptz
                    USING updated_at AT TIME ZONE 'UTC'
        ");

        DB::statement(<<<'SQL'
            ALTER TABLE users
                ADD CONSTRAINT users_status_check
                    CHECK (status IN ('active', 'suspended')),

                ADD CONSTRAINT users_login_credentials_check
                    CHECK (
                        mobile IS NOT NULL
                        OR (
                            email IS NOT NULL
                            AND password IS NOT NULL
                        )
                    ),

                ADD CONSTRAINT users_mobile_format_check
                    CHECK (
                        mobile IS NULL
                        OR mobile ~ '^\+[1-9][0-9]{1,14}$'
                    ),

                ADD CONSTRAINT users_email_normalized_check
                    CHECK (
                        email IS NULL
                        OR (
                            email <> ''
                            AND email = LOWER(BTRIM(email))
                        )
                    ),

                ADD CONSTRAINT users_mobile_verification_check
                    CHECK (
                        mobile_verified_at IS NULL
                        OR mobile IS NOT NULL
                    ),

                ADD CONSTRAINT users_email_verification_check
                    CHECK (
                        email_verified_at IS NULL
                        OR email IS NOT NULL
                    )
        SQL);
    }

    public function down(): void
    {
        if (DB::table('users')
            ->whereNull('name')
            ->orWhereNull('email')
            ->orWhereNull('password')
            ->exists()) {
            throw new \RuntimeException(
                'Existing users are incompatible with the previous schema.'
            );
        }

        DB::statement("
            ALTER TABLE users
                DROP CONSTRAINT users_status_check,
                DROP CONSTRAINT users_login_credentials_check,
                DROP CONSTRAINT users_mobile_format_check,
                DROP CONSTRAINT users_email_normalized_check,
                DROP CONSTRAINT users_mobile_verification_check,
                DROP CONSTRAINT users_email_verification_check,

                ALTER COLUMN name SET NOT NULL,
                ALTER COLUMN email SET NOT NULL,
                ALTER COLUMN password SET NOT NULL,
                ALTER COLUMN email_verified_at
                    TYPE timestamp(0) without time zone
                    USING email_verified_at AT TIME ZONE 'UTC',
                ALTER COLUMN created_at
                    TYPE timestamp(0) without time zone
                    USING created_at AT TIME ZONE 'UTC',
                ALTER COLUMN updated_at
                    TYPE timestamp(0) without time zone
                    USING updated_at AT TIME ZONE 'UTC'
        ");

        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['role_id']);
            $table->dropIndex(['role_id']);
            $table->dropUnique(['mobile']);

            $table->dropColumn([
                'role_id',
                'mobile',
                'status',
                'mobile_verified_at',
            ]);
        });
    }
};
