<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateFormMapTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('form_map', function (Blueprint $table) {
            $table->id();
            $table->string('topic_name'); // Topic name for grouping questions and answers
            $table->unsignedBigInteger('question_id');
            $table->unsignedBigInteger('answer_id');
            $table->foreign('question_id')->references('id')->on('questions')->onDelete('no action');
            $table->foreign('answer_id')->references('id')->on('answers')->onDelete('no action');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('form_map');
    }
}
