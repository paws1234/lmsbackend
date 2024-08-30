<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Student extends Model
{
    use HasFactory;
    protected $table = 'students';
    protected $fillable = ['name', 'email', 'password', 'user_id'];
    protected $hidden = ['password', 'remember_token'];
    public function user()
    {
        return $this->belongsTo(User::class);
    }
    public function enrollments()
    {
        return $this->hasMany(Enrollment::class);
    }

}