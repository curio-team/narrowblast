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
        Schema::create('invite_system_invitees', function (Blueprint $table) {
            $table->id();

            $table->foreignUuid('invite_system_id')->references('id')->on('invite_systems')->cascadeOnDelete();
            $table->string('user_id')->references('id')->on('users')->cascadeOnDelete();
            $table->unsignedInteger('reserved_entry_fee_in_credits')->nullable();

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
        Schema::dropIfExists('invite_system_invitees');
    }
};
