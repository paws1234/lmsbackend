<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateScoresTable extends Migration
{
    public function up()
    {
        Schema::create('scores', function (Blueprint $table) {
            $table->id();  // Auto-incremented primary key
            $table->foreignId('student_id')->constrained('students')->onDelete('cascade');  // Foreign key to students
            $table->foreignId('subject_id')->constrained('subjects')->onDelete('cascade');  // Foreign key to subjects
            $table->foreignId('question_id')->constrained('questions')->onDelete('cascade');  // Foreign key to questions
            $table->foreignId('form_map_id')->constrained('form_map')->onDelete('cascade');  // Foreign key to form_maps
            $table->foreignId('enrollment_id')->constrained('enrollments')->onDelete('cascade');  // Foreign key to enrollments
            $table->integer('points');  // Store the points scored
            $table->timestamps();  // Timestamp for created and updated dates
        });
    }

    public function down()
    {
        Schema::dropIfExists('scores');  // Drop the scores table if rolled back
    }
}
