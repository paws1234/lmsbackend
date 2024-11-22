<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Score extends Model
{
    use HasFactory;

    protected $fillable = ['student_id', 'task_id', 'score'];

    public function student()
    {
        return $this->belongsTo(Student::class);
    }

    public function task()
    {
        return $this->belongsTo(Todo::class);  
    }

    
    public static function calculateScore($studentId, $taskId)
    {
        
        $correctAnswersCount = Answer::where('task_id', $taskId)
                                     ->where('is_correct', true)
                                     ->where('student_id', $studentId)
                                     ->count();

        
        return $correctAnswersCount;
    }
}
