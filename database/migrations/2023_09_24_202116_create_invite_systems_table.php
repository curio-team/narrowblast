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
        Schema::create('invite_systems', function (Blueprint $table) {
            $table->uuid('id')->primary();

            $table->foreignId('shop_item_user_id')->nullable()->references('id')->on('shop_item_user')->cascadeOnDelete(); // Null on preview
            $table->string('user_id')->references('id')->on('users')->cascadeOnDelete(); // Needed because shop_item_user_id can be null sometimes

            $table->string('title');
            $table->string('latest_code')->nullable()->unique();
            $table->string('description');

            $table->unsignedInteger('invitee_slots')->nullable();
            $table->unsignedInteger('entry_fee_in_credits')->nullable();

            $table->json('data')->nullable();

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
        Schema::dropIfExists('invite_systems');
    }
};
