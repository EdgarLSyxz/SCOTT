<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('solar_interferences', function (Blueprint $table) {
            $table->index('document_name', 'solar_interferences_document_name_idx');
            $table->index(['document_name', 'event_date'], 'solar_interferences_doc_date_idx');
        });
    }

    public function down(): void
    {
        Schema::table('solar_interferences', function (Blueprint $table) {
            $table->dropIndex('solar_interferences_document_name_idx');
            $table->dropIndex('solar_interferences_doc_date_idx');
        });
    }
};