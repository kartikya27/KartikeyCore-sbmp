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

        Schema::create(CORE_CONFIG_TABLE, function (Blueprint $table) {
            $table->id();
            $table->string('code');
            $table->text('value');
            $table->string('channel_code')->nullable();
            $table->string('locale_code')->nullable();
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists(CORE_CONFIG_TABLE);
    }
};
