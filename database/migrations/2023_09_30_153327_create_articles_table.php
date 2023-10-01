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
        Schema::create('articles', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->unsignedBigInteger('grammar_id');
            $table->foreign('grammar_id')->references('id')->on('grammars')->onDelete('cascade');
            $table->unsignedBigInteger('technology_id');
            $table->foreign('technology_id')->references('id')->on('technologies')->onDelete('cascade');
            $table->integer('intensive_reading_cycle')->nullable();
            $table->integer('reading_around_cycle')->nullable();
            $table->integer('reading_around_count')->nullable();
            $table->text('article');
            $table->text('article_jp');
            $table->text('grammar_explanation');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('articles');
    }
};
