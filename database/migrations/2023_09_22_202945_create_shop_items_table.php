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

            // Unique name used to find an item from code
            $table->string('unique_id')->unique();

            $table->text('description');
            $table->string('image_path', 512)->nullable();

            $table->integer('cost_in_credits')->unsigned();
            $table->unsignedInteger('max_per_user')->nullable();

            $table->string('required_type')->nullable();

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
