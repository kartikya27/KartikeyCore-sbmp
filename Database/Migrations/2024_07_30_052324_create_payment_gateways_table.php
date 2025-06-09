<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {

        Schema::create(PAYMENT_GATEWAY_TABLE, function (Blueprint $table) {
            $table->id();
            $table->string('method')->nullable();
            $table->string('app_name')->nullable();
            $table->string('app_id')->nullable();
            $table->string('secret')->nullable();
            $table->string('key')->nullable();
            $table->string('success_url')->nullable();
            $table->string('cancel_url')->nullable();
            $table->integer('mode')->default(1);
            $table->integer('status')->default(1);
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists(PAYMENT_GATEWAY_TABLE);
    }
};
