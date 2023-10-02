<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ArticleTestWordGrouping extends Model
{
    use HasFactory;

    protected $fillable = [
        'article_test_id',
        'name',
        'word_test_group',
        'save_flag',
    ];
}
