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
        // Items purchased by users
        Schema::create('shop_item_user', function (Blueprint $table) {
            $table->id();

            $table->foreignId('shop_item_id')->references('id')->on('shop_items')->cascadeOnDelete();
            $table->string('user_id')->references('id')->on('users');

            $table->integer('cost_in_credits')->unsigned();

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
        Schema::dropIfExists('shop_item_user');
    }
};
