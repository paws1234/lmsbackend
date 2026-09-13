<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTeachersTable extends Migration
{
    public function up()
    {
        Schema::create('teachers', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('email')->unique();
            // Unique because `subjects.teacher_id` and `tasks.teacher_id` both
            // point at this column.  MySQL is happy to reference a merely
            // indexed column, but PostgreSQL refuses a foreign key unless the
            // referenced column has a unique constraint.
            $table->unsignedBigInteger('user_id')->unique();
            $table->timestamps();

            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::table('teachers', function (Blueprint $table) {
            $table->dropForeign(['user_id']);
        });

        Schema::dropIfExists('teachers');
    }
}