<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('solar_interference_uploads', function (Blueprint $table) {
            $table->id();
            $table->string('document_name')->unique();
            $table->unsignedInteger('records_count')->default(0);
            $table->unsignedInteger('states_count')->default(0);
            $table->unsignedInteger('satellites_count')->default(0);
            $table->unsignedInteger('teleports_count')->default(0);
            $table->date('first_event_date')->nullable();
            $table->date('last_event_date')->nullable();
            $table->boolean('is_active')->default(true);
            $table->foreignId('uploaded_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['is_active', 'last_event_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('solar_interference_uploads');
    }
};
