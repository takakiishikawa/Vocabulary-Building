<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class article extends Model
{
    use HasFactory;

    protected $fillable = [
        'grammar_id',
        'technology_id',
        'article',
        'article_jp',
        'grammar_explanation',
        'article_practice_result',
        'initial_test_result',
        'cumulative_incorrect_count'
    ];

    public function grammar()
    {
        return $this->belongsTo(Grammar::class);
    }

    public function technology()
    {
        return $this->belongsTo(Technology::class);
    }
}
