<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddFieldsInBankData extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('bank_data', function (Blueprint $table) {            
            $table->string('pan_photo')->nullable();
            $table->string('aadhar_photo')->nullable();
            $table->string('passbook_photo')->nullable();            
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('bank_data', function (Blueprint $table) {
            //
        });
    }
}
