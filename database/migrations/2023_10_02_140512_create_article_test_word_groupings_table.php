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
        Schema::create('article_test_word_groupings', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->integer('article_test_generate_id');
            $table->string('name');
            $table->integer('word_test_group');
            $table->integer('save_flag');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('article_test_word_groupings');
    }
};
