<?php

// app/Models/Submission.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Submission extends Model
{
    use HasFactory;

    protected $fillable = [
        'form_map_id',
        'question_id',
        'answer_id',
        'user_id',
        'is_correct',
    ];

    public function formMap()
    {
        return $this->belongsTo(FormMap::class);
    }

    public function question()
    {
        return $this->belongsTo(Question::class);
    }

    public function answer()
    {
        return $this->belongsTo(Answer::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // Method to check if the chosen answer is correct
    public static function checkAnswer($question_id, $answer_id)
    {
        $answer = Answer::where('question_id', $question_id)
            ->where('id', $answer_id)
            ->first();

        return $answer && $answer->is_correct;
    }
}
