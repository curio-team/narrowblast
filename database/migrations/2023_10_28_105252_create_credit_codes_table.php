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
        Schema::create('credit_codes', function (Blueprint $table) {
            $table->id();

            $table->string('code')->unique();
            $table->integer('credits')->default(0);
            $table->timestamp('redeemed_at')->nullable();
            $table->string('redeemed_by')->nullable()->constrained('users')->cascadeOnDelete();
            $table->string('created_by')->nullable()->constrained('users')->cascadeOnDelete();

            $table->timestamp('printed_at')->nullable();

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
        Schema::dropIfExists('credit_codes');
    }
};
