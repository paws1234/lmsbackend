<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddTeacherIdToSchedulesTable extends Migration
{
    public function up()
    {
        Schema::create('schedules', function (Blueprint $table) {
            $table->id();
            $table->string('day'); // e.g., Monday, Tuesday, etc.
            $table->time('time_in');
            $table->time('time_out');
            $table->timestamps();
            $table->string('room');
            $table->unsignedBigInteger('teacher_id');
            $table->foreign('teacher_id')->references('id')->on('teachers')->onDelete('cascade');
        });
    }

    public function down()
    {
        // up() creates the whole `schedules` table, despite what the migration
        // is called.  Dropping only the column left the table in place, so
        // `migrate:rollback` followed by `migrate` died on "table already
        // exists" — dropping the table is what actually undoes up().
        Schema::dropIfExists('schedules');
    }
}