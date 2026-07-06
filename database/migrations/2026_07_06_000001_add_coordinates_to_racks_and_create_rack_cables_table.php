<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('racks', function (Blueprint $table) {
            $table->decimal('latitude', 10, 7)->nullable()->after('description');
            $table->decimal('longitude', 10, 7)->nullable()->after('latitude');

            $table->index(['latitude', 'longitude'], 'racks_lat_lng_index');
        });

        Schema::create('rack_cables', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('rack_id');
            $table->unsignedInteger('source_position');
            $table->unsignedBigInteger('source_equipment_id')->nullable();
            $table->string('source_port', 40)->nullable();
            $table->string('destination_label', 160);
            $table->string('destination_ip', 64)->nullable();
            $table->unsignedSmallInteger('vlan')->nullable();
            $table->string('cable_type', 40)->nullable();
            $table->string('color', 20)->nullable();
            $table->text('notes')->nullable();
            $table->boolean('is_active')->default(true);
            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->timestamps();

            $table->foreign('rack_id')
                ->references('id')
                ->on('racks')
                ->onDelete('cascade');

            $table->foreign('source_equipment_id')
                ->references('id')
                ->on('rack_equipment')
                ->onDelete('set null');

            $table->index(['rack_id', 'source_position']);
            $table->index(['rack_id', 'vlan']);
            $table->index(['rack_id', 'cable_type']);
            $table->index('is_active');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('rack_cables');

        Schema::table('racks', function (Blueprint $table) {
            $table->dropIndex('racks_lat_lng_index');
            $table->dropColumn(['latitude', 'longitude']);
        });
    }
};
