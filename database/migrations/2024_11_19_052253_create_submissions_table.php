<?php
// database/migrations/xxxx_xx_xx_create_submissions_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSubmissionsTable extends Migration
{
    public function up()
    {
        Schema::create('submissions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('form_map_id')->constrained('form_map')->onDelete('cascade');
            $table->foreignId('question_id')->constrained('questions')->onDelete('cascade');
            $table->foreignId('answer_id')->constrained('answers')->onDelete('cascade');
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade'); // Assuming users are present
            $table->boolean('is_correct')->default(false); // Determines if the chosen answer is correct
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('submissions');
    }
}
