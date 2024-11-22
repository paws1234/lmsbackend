<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTodosTable extends Migration
{
    public function up()
    {
        Schema::create('todos', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('teacher_id')->nullable(); 
            $table->unsignedBigInteger('subject_id'); // Add the subject_id column
            $table->string('type');
            $table->string('title');
            $table->text('description')->nullable();
            $table->string('file')->nullable();
            $table->timestamps();

            // Foreign keys
            $table->foreign('teacher_id')->references('id')->on('teachers')->onDelete('set null');
            $table->foreign('subject_id')->references('id')->on('subjects')->onDelete('cascade'); // Add the foreign key for subject_id
        });
    }

    public function down()
    {
        Schema::table('todos', function (Blueprint $table) {
            $table->dropForeign(['teacher_id']);
            $table->dropForeign(['subject_id']); // Drop the foreign key for subject_id
        });

        Schema::dropIfExists('todos');
    }
}
