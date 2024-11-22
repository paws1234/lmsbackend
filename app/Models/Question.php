<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Question extends Model
{
    protected $fillable = ['question_text', 'points','teacher_id','subject_id'];

    public function answers()
    {
        return $this->hasMany(Answer::class);
    }

    public function formMaps()
    {
        return $this->hasMany(FormMap::class, 'question_id');
    }

    public function teacher()
    {
        return $this->belongsTo(Teacher::class);
    }

    public function subject()
    {
        return $this->belongsTo(Subject::class, 'subject_id', 'id');
    }
}
