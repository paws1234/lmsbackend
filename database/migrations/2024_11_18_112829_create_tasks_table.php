<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTasksTable extends Migration
{
    public function up()
    {
        Schema::create('tasks', function (Blueprint $table) {
            $table->id();
            // This will automatically create a foreign key constraint for teacher_id
            $table->foreignId('teacher_id')->nullable()->constrained('teachers', 'user_id')->onDelete('set null');
            // These will automatically create foreign key constraints for subject_id and enrollment_id
            $table->foreignId('subject_id')->constrained('subjects')->onDelete('cascade');
            $table->foreignId('enrollment_id')->constrained('enrollments')->onDelete('cascade');
            $table->string('type'); // Type of the task (e.g., 'todo', 'question')
            $table->string('title');
            $table->text('description')->nullable();
            $table->string('file')->nullable();
            $table->unsignedBigInteger('form_map_id')->nullable(); // Reference to form_map for questions
            $table->timestamps();

            // This will automatically create a foreign key constraint for form_map_id
            $table->foreign('form_map_id')->references('id')->on('form_map')->onDelete('set null');
        });
    }

    public function down()
    {
        Schema::table('tasks', function (Blueprint $table) {
            $table->dropForeign(['teacher_id']);
            $table->dropForeign(['subject_id']);
            $table->dropForeign(['enrollment_id']);
            $table->dropForeign(['form_map_id']);
        });

        Schema::dropIfExists('tasks');
    }
}
