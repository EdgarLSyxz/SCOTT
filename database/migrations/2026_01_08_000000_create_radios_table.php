<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (! Schema::hasTable('radios')) {
            Schema::create('radios', function (Blueprint $table) {
                $table->bigIncrements('id');
                $table->string('name');
                $table->string('url')->nullable();
                $table->string('image_url')->nullable();
                $table->timestamps();
            });
        } else {
            if (! Schema::hasColumn('radios', 'image_url')) {
                Schema::table('radios', function (Blueprint $table) {
                    $table->string('image_url')->nullable()->after('url');
                });
            }
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        if (Schema::hasTable('radios')) {
            if (Schema::hasColumn('radios', 'image_url')) {
                Schema::table('radios', function (Blueprint $table) {
                    $table->dropColumn('image_url');
                });
            }
        }
    }
};
