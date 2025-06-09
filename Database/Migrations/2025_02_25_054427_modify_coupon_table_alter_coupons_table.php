<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::table(COUPON_TABLE, function (Blueprint $table) {
            // Drop the unique constraint from 'code'
            $table->dropUnique(['code']);

            // Ensure 'seller_id' column exists
            if (!Schema::hasColumn(COUPON_TABLE, 'seller_id')) {
                $table->unsignedBigInteger('seller_id')->after('id');
            }

            // Add composite unique constraint (code, seller_id)
            $table->unique(['code', 'seller_id']);
        });
    }

    public function down()
    {
        Schema::table(COUPON_TABLE, function (Blueprint $table) {
            // Drop the composite unique constraint
            $table->dropUnique(['code', 'seller_id']);

            // Reapply unique constraint to 'code' (if needed)
            $table->unique('code');
        });
    }
};
