<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSheetMatchingTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('sheet_matchings', function (Blueprint $table) {
            $table->id();
            $table->string('bank_id');
            $table->string('product_id');
            $table->string('group');
            $table->string('app_id');
            $table->string('location');
            $table->string('name');
            $table->string('disbAmount');
            $table->string('payout_amount');
            $table->string('payout_rate');
            $table->string('date');
            $table->string('month');
            $table->string('pf%');
            $table->string('kli');
            $table->string('kli_payout%');
            $table->string('kli_payout');
            $table->string('kgi');
            $table->string('kgi_payout%');
            $table->string('kgi_payout');
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
        Schema::dropIfExists('sheet_matching');
    }
}
