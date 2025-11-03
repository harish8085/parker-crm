<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class BankMis extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('bank_mis', function (Blueprint $table) {
            $table->id();
            $table->foreignId('bank_id')->constrained()->onDelete('cascade');
            $table->foreignId('product_id')->constrained()->onDelete('cascade');
            $table->string('group');
            $table->string('app_id');
            $table->string('location');
            $table->string('customer_name');
            $table->string('disbAmount');
            $table->string('payout_amount');
            $table->string('payout_rate');
            $table->string('case_location');
            $table->string('customer_firm_name');
            $table->string('pf');
            $table->string('subvention');
            $table->string('roi');
            $table->string('insurance');
            $table->string('otc_pdd_status');
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
        Schema::dropIfExists('bank_mis');
    }
}
