<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('verification_documents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('case_id')->constrained('identity_verification_cases')->restrictOnDelete();
            $table->string('kind', 30);
            $table->text('storage_key');
            $table->string('mime_type', 100);
            $table->bigInteger('size_bytes');
            $table->timestampTz('created_at')->useCurrent();
            $table->unique([
                'case_id',
                'kind',
            ], 'vd_case_kind_unique');
        });

        DB::statement(<<<'SQL'
            ALTER TABLE verification_documents
                ADD CONSTRAINT vd_kind_check
                    CHECK (
                        kind IN (
                            'national_id_image',
                            'latest_diploma_image'
                        )
                    ),

                ADD CONSTRAINT vd_storage_key_check
                    CHECK (
                        storage_key ~ '[^[:space:]]'
                    ),

                ADD CONSTRAINT vd_mime_type_check
                    CHECK (
                        mime_type ~ '[^[:space:]]'
                    ),

                ADD CONSTRAINT vd_size_bytes_check
                    CHECK (
                        size_bytes > 0
                    )
        SQL);
    }

    public function down(): void
    {
        Schema::dropIfExists('verification_documents');
    }
};
