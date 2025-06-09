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

        Schema::create(COUPON_USE_TABLE, function (Blueprint $table) {
            $table->id();
            $table->foreignId('coupon_id')->constrained(COUPON_TABLE)->cascadeOnDelete();
            $table->foreignId('user_id')->constrained(USER_TABLE)->cascadeOnDelete();
            $table->foreignId('order_id')->constrained(ORDER_TABLE)->cascadeOnDelete();
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists(COUPON_USE_TABLE);
    }
};
