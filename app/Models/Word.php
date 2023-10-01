<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Word extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'frequency',
        'parse',
        'imagery',
        'core_meaning',
        'word_jp',
        'initial_test_result',
        'word_practice_result',
        'cumulative_incorrect_count'
    ];
}
