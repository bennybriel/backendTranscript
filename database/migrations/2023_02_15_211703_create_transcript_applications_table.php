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
        Schema::create('transcript_applications', function (Blueprint $table) {
            $table->id();
            $table->string('matricno');
            $table->string('email');
            $table->integer('programme');
            $table->string('name');
            $table->string('state');
            $table->integer('country');
            $table->string('phone');
            $table->boolean('status');
            $table->boolean('ispaid');
            $table->string('guid');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('transcript_applications');
    }
};
