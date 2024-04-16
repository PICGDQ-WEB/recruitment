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
        Schema::create('blog_seo', function (Blueprint $table) {
            $table->id();
            $table->string('title')->nullable();
            $table->string('canonical')->nullable();
            $table->string('description')->nullable();
            $table->string('keywords')->nullable();
            $table->string('file')->nullable();
            $table->string('author')->nullable();
            $table->string('smarkup')->nullable();
            $table->unsignedBigInteger('blogid');
            $table->foreign('blogid')->references('id')->on('blogs');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('blog_seo');
    }
};
