<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('provider_match_results', function (Blueprint $table) {
            $table->id();
            $table->foreignId('case_id')->constrained('identity_verification_cases')->restrictOnDelete();
            $table->string('provider', 100);
            $table->string('outcome', 20);
            $table->boolean('is_simulated');
            $table->string('provider_reference', 255)->nullable();
            $table->timestampTz('checked_at');
        });

        DB::statement(<<<'SQL'
            ALTER TABLE provider_match_results
                ADD CONSTRAINT pmr_provider_check
                    CHECK (
                        provider ~ '[^[:space:]]'
                    ),

                ADD CONSTRAINT pmr_outcome_check
                    CHECK (
                        outcome IN (
                            'matched',
                            'mismatched',
                            'unavailable',
                            'error'
                        )
                    ),

                ADD CONSTRAINT pmr_provider_reference_check
                    CHECK (
                        provider_reference IS NULL
                        OR provider_reference ~ '[^[:space:]]'
                    )
        SQL);

        DB::statement(<<<'SQL'
            CREATE INDEX pmr_case_checked_at_index
            ON provider_match_results (
                case_id,
                checked_at DESC
            )
        SQL);
    }

    public function down(): void
    {
        Schema::dropIfExists('provider_match_results');
    }
};
