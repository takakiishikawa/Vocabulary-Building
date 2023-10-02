<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ArticleTestGenerate extends Model
{
    use HasFactory;

    protected $fillable = [
        'grammar_id',
        'technology_id',
        'article',
        'article_jp',
        'grammar_explanation',
        'word_frequency_average',
        'save_flag',
    ];
}