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
        Schema::create('swPicture', function (Blueprint $table) {
            $table->id();
            $table->integer('type'); //0 = Product 1 = ProductClass
            $table->unsignedBigInteger('assignment_id');
            $table->string('path');
            $table->string('file');
            $table->integer('pos');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('swPicture');
    }
};
