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
        Schema::table('article_test_generates', function (Blueprint $table) {
            //nullableに変更する
            $table->string('article_jp')->nullable()->change();
            $table->string('grammar_explanation')->nullable()->change();
            $table->integer('word_frequency_average')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('article_test_generates', function (Blueprint $table) {
            //
        });
    }
};
