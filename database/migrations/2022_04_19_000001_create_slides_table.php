<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration {
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('slides', function (Blueprint $table) {
            $table->bigIncrements('id');

            $table->string('title');
            $table->string('user_id')->references('id')->on('users')->cascadeOnDelete();
            $table->string('path', 512);

            $table->json('data')->nullable();

            $table->dateTime('finalized_at')->nullable();

            // Which teacher approved this slide and when
            $table->string('approver_id')->nullable()->references('id')->on('users')->cascadeOnDelete();
            $table->dateTime('approved_at')->nullable();

            $table->string('rejecter_id')->nullable()->references('id')->on('users')->cascadeOnDelete();
            $table->dateTime('rejected_at')->nullable();
            $table->string('rejection_reason')->nullable();

            $table
                ->foreign('approver_id')
                ->references('id')
                ->on('users')
                ->onUpdate('CASCADE')
                ->onDelete('CASCADE');

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
        Schema::dropIfExists('slides');
    }
};
