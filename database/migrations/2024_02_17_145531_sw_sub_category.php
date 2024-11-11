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
        Schema::create('swSubCategory', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('swCategory_id');
            $table->string('title');
            $table->string('title_en')->nullable();

            $table->string('meta_title')->nullable();
            $table->string('meta_description')->nullable();
            $table->string('meta_keywords')->nullable();

            $table->string('sw_id');
            $table->boolean('sw_edited')->default(0);
            $table->boolean('sw_deleted')->default(0);
            $table->boolean('sw_active')->default(0);

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('swSubCategory');
    }
};
