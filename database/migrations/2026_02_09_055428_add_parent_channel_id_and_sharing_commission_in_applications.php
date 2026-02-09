<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddParentChannelIdAndSharingCommissionInApplications extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('applications', function (Blueprint $table) {
            $table->unsignedBigInteger('parent_channel_id')->nullable();
            $table->foreign('parent_channel_id')->references('id')->on('users')->onDelete('cascade')->onUpdate('cascade');
            $table->string('sharing_commission')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('applications', function (Blueprint $table) {
            $table->dropColumn('parent_channel_id'); $table->dropForeign(['parent_channel_id']);            
            $table->dropColumn('sharing_commission');
        });
    }
}
