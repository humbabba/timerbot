<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTimersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('timers', function (Blueprint $table) {
            $table->id();
            $table->text('name');
            $table->unsignedInteger('guys')->default(8);
            $table->unsignedInteger('current_guy')->default(0);
            $table->time('current_guy_start')->nullable();
            $table->time('end_time')->default('20:58:00');
            $table->boolean('started')->default(0);
            $table->text('current_guy_alarm_status')->nullable();
            $table->longText('message')->nullable();
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
        Schema::dropIfExists('timers');
    }
}
