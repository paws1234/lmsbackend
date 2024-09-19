<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FormMap extends Model
{
    protected $table = 'form_map'; 
    
    protected $fillable = ['topic_name', 'question_id', 'answer_id'];

    public function question()
    {
        return $this->belongsTo(Question::class);
    }

    public function answer()
    {
        return $this->belongsTo(Answer::class);
    }
}
