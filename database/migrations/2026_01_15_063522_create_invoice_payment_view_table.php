<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateInvoicePaymentViewTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('invoice_payment_view', function (Blueprint $table) {
            $table->id();
            $table->string('invoice_no');
            $table->string('invoice_date');
            $table->string('bank_name');
            $table->string('bank_address');
            $table->string('payment_amount');
            $table->string('bank_gst_no');
            $table->string('bank_hsn_code');
            $table->string('dsa_pan');
            $table->string('dsa_gst_no');
            $table->string('application_no');
            $table->string('CGST_amount')->nullable();
            $table->string('SGST_amount')->nullable();
            $table->string('IGST_amount')->nullable();
            $table->string('invoive_value')->nullable();
            $table->string('taxable_value')->nullable();
            $table->string('payment_recevied_bank')->nullable();
            $table->string('remaining_amount')->nullable();
            $table->string('payment_status')->nullable();

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
        Schema::dropIfExists('invoice_payment_view');
    }
}
