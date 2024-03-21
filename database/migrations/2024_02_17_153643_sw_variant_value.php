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
        Schema::create('swVariantValue', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('swProduct_id');

            $table->text('value');
            $table->text('value_en')->nullable();
            $table->integer('pos');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('swVariantValue');
    }
};
