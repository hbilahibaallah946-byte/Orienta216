<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StudentRecord extends Model
{
    protected $fillable = [
        'user_id',
        'section',
        'session',
        'math',
        'physics',
        'science',
        'arabic',
        'french',
        'english',
        'philosophy',
        'info',
        'option',
        'sport',
        'average',
        'score',
        'score_boosted'
    ];
}
