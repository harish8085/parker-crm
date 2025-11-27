<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAdvanceAmountLogs extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('advance_amount_logs', function (Blueprint $table) {
            $table->increments('id');
            $table->bigInteger('advance_id')->unsigned()->nullable();
            $table->foreign('advance_id')->references('id')->on('advances')->onDelete('cascade')->onUpdate('cascade');
            $table->string('advance_amount')->nullable();
            $table->string('advance_date')->nullable();   
            $table->string('type')->nullable();     // add, deduct             
            $table->text('remark')->nullable();     // remark for the action
            $table->bigInteger('created_by')->unsigned()->nullable();     // created by
            $table->foreign('created_by')->references('id')->on('users')->onDelete('cascade')->onUpdate('cascade');
            $table->string('application_ids')->nullable();               
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
        Schema::dropIfExists('advance_amount_logs');
    }
}
