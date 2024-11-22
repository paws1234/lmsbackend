<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Task extends Model
{
    use HasFactory;

    protected $fillable = [
        'teacher_id',
        'subject_id',
        'enrollment_id',
        'type',
        'title',
        'description',
        'file',
        'form_map_id',
    ];

    /**
     * The task belongs to a teacher.
     */
    public function teacher()
    {
        return $this->belongsTo(Teacher::class, 'teacher_id');
    }

    /**
     * The task belongs to a subject.
     */
    public function subject()
    {
        return $this->belongsTo(Subject::class);
    }

    /**
     * The task belongs to an enrollment.
     */
    public function enrollment()
    {
        return $this->belongsTo(Enrollment::class);
    }

    /**
     * The task may be associated with a form map (for questions).
     */
    public function formMap()
    {
        return $this->belongsTo(FormMap::class, 'form_map_id');
    }
}
