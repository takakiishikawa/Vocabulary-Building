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
        Schema::create('words', function (Blueprint $table) {
            $table->id();
            $table->string('name')->nullable();
            $table->string('frequency')->nullable();
            $table->string('parse')->nullable();
            $table->string('core_meaning')->nullable();
            $table->string('imagery')->nullable();
            $table->string('jp_word')->nullable();
            $table->boolean('initial_test_result')->nullable();
            $table->boolean('word_practice_result')->nullable();
            $table->integer('cumulative_incorrect_count')->nullable();  
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('words');
    }
};
