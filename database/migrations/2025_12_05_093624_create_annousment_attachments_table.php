<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Testing\Constraints\SoftDeletedInDatabase;

class CreateAnnousmentAttachmentsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('announcement_attachments', function (Blueprint $table) {
            $table->id()->autoIncrement();
            $table->bigInteger('announcement_id')->unsigned()->nullable();
            $table->foreign('announcement_id')->references('id')->on('announcements')->onDelete('cascade')->onUpdate('cascade');
            $table->string('attachment');
            $table->bigInteger('created_by')->unsigned()->nullable();
            $table->foreign('created_by')->references('id')->on('users')->onDelete('cascade')->onUpdate('cascade');
            $table->softDeletes();
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
        Schema::table('announcement_attachments', function (Blueprint $table) {
            //
        });
    }
}
