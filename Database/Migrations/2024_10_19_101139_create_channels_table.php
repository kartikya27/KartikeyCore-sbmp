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

        Schema::create(CHANNEL_TABLE, function (Blueprint $table) {
            $table->id();

            $table->string('code');
            $table->string('timezone')->nullable();
            $table->string('theme')->nullable();
            $table->string('hostname')->nullable();
            $table->string('logo')->nullable();
            $table->string('favicon')->nullable();
            $table->json('home_seo')->nullable();
            $table->boolean('is_maintenance_on')->default(0);
            $table->text('allowed_ips')->nullable();
            $table->foreignId('root_category_id')->nullable()->constrained(CATEGORY_TABLE)->cascadeOnDelete();
            $table->foreignId('default_locale_id')->nullable()->constrained(LOCALE_TABLE)->cascadeOnDelete();
            $table->integer('base_currency_id');

            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists(CHANNEL_TABLE);
    }
};
