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
        Schema::create('screen_slides', function (Blueprint $table) {
            $table->id();

            $table->foreignId('screen_id')->references('id')->on('screens')->cascadeOnDelete();
            $table->foreignId('slide_id')->references('id')->on('slides')->cascadeOnDelete();
            $table->string('activator_id')->references('id')->on('users')->cascadeOnDelete();
            $table->unsignedInteger('slide_order')->default(0);
            $table->unsignedInteger('slide_duration')->nullable();

            $table->dateTime('displays_from')->useCurrent();
            $table->dateTime('displays_until')->nullable();

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
        Schema::dropIfExists('screen_slides');
    }
};
