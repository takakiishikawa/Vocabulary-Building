<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WordTest extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'parse',
        'core_meaning',
        'imagery',
        'word_jp',
        'save_flag',
    ];
}