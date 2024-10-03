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
            $table->string('type');
            $table->string('title');
            $table->text('description')->nullable();
            $table->string('file')->nullable();
            $table->timestamps();
            $table->foreign('teacher_id')->references('user_id')->on('teachers')->onDelete('set null');
        });
    }

    public function down()
    {
        Schema::table('todos', function (Blueprint $table) {
            $table->dropForeign(['teacher_id']);
        });

        Schema::dropIfExists('todos');
    }
}
