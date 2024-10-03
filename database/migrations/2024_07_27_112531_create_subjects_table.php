<?php

// database/migrations/xxxx_xx_xx_xxxxxx_create_subjects_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSubjectsTable extends Migration
{
    public function up()
    {
        Schema::create('subjects', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->text('description')->nullable();
            $table->unsignedBigInteger('teacher_id')->nullable(); 
            $table->timestamps();
            
            // Update foreign key reference to user_id
            $table->foreign('teacher_id')->references('user_id')->on('teachers')->onDelete('set null');
        });
    }

    public function down()
    {
        Schema::table('subjects', function (Blueprint $table) {
            $table->dropForeign(['teacher_id']);
        });

        Schema::dropIfExists('subjects');
    }
}
