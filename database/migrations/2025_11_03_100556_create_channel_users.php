<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateChannelUsers extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('channel_users', function (Blueprint $table) {
            $table->id();
    
            $table->unsignedBigInteger('channel_id')->nullable();
            $table->foreign('channel_id')->references('id')->on('users')->onDelete('cascade')->onUpdate('cascade');
    
            $table->unsignedBigInteger('associate_channel_id')->nullable();
            $table->foreign('associate_channel_id')->references('id')->on('users')->onDelete('cascade')->onUpdate('cascade');
    
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
        Schema::dropIfExists('channel_users');
    }
}
