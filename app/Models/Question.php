<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Question extends Model
{
    protected $fillable = ['question_text', 'points'];

    public function answers()
    {
        return $this->hasMany(Answer::class);
    }

    public function formMaps()
    {
        return $this->hasMany(FormMap::class, 'question_id');
    }
}
