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
        // Items available for purchase in the shop
        Schema::create('shop_items', function (Blueprint $table) {
            $table->id();

            $table->string('name');
            $table->string('description');
            $table->string('image_path')->nullable();

            $table->integer('cost_in_credits')->unsigned();
            $table->unsignedInteger('max_per_user')->nullable();

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
        Schema::dropIfExists('shop_items');
    }
};
