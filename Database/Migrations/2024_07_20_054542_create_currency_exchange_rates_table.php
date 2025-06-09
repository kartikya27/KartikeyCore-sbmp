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
        Schema::create(CURRENCY_EXCHANGE_RATE_TABLE, function (Blueprint $table) {
            $table->increments('id');
            $table->decimal('rate', 24, 12);
            $table->integer('target_currency')->unique()->unsigned();
            $table->foreign('target_currency')->references('id')->on('currencies')->onDelete('cascade');
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
        Schema::dropIfExists(CURRENCY_EXCHANGE_RATE_TABLE);
    }
};
