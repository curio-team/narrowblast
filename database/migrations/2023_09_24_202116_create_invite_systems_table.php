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

            $table->foreignId('shop_item_user_id')->references('id')->on('shop_item_user')->cascadeOnDelete();

            $table->string('title');
            $table->string('description');
            $table->boolean('is_preview')->default(true);

            $table->unsignedInteger('invitee_slots')->nullable();
            $table->unsignedInteger('entry_free_in_credits')->nullable();

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
