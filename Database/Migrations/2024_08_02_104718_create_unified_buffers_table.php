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

        Schema::create(UNIFIED_BUFFER_TABLE, function (Blueprint $table) {
            $table->id();
            $table->string('type')->comment('e.g. category,company,brand');
            $table->json('data');
            $table->integer('status')->default(0);
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists(UNIFIED_BUFFER_TABLE);
    }
};
