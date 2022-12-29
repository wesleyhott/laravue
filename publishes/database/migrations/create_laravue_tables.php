<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateLaravueTables extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('files', function (Blueprint $table) {
            $table->id('id');
            $table->string('path');
            $table->string('name');
            /**
             * Type: image, audio, video, document, unknown
             */
            $table->string('type', 20)->nullable();
            /**
             * Subtype: default configuration is given by file extension
             */
            $table->string('subtype', 20)->nullable();
            $table->integer('bytes')->default(0);
            /**
             * Access Level:
             * 
             * 0: public, 1: protected, 2: top_secret, ...
             */
            $table->integer('access_level')->default(0);
            $table->timestamps();
        });

        Schema::table('users', function (Blueprint $table) {
            $table->foreignId('avatar_id')
                ->nullable()
                ->constrained('files');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('avatar_id');
        });
        Schema::dropIfExists('files');
    }
}
