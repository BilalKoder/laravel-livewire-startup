<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAppointmentsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('appointments', function (Blueprint $table) {
            $table->id();
            $table->string('topic');
            $table->text('message');
            $table->string('preferred_date');
            $table->string('preferred_time');
            $table->enum('status', ['PENDING', 'APPROVED', 'COMPLETED'])->default('PENDING');
            $table->bigInteger('user_id');
            $table->string('coach_id')->nullable();
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
        Schema::dropIfExists('appointments');
    }
}
