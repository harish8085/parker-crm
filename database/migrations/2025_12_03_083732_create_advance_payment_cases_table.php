<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAdvancePaymentCasesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('advance_payment_cases', function (Blueprint $table) {
            $table->id();
            // Match type of advance_amount_logs.id which is increments() (unsigned INT)
            $table->unsignedInteger('advance_amount_log_id')->nullable();
            $table->foreign('advance_amount_log_id')
                ->references('id')
                ->on('advance_amount_logs')
                ->onDelete('cascade')
                ->onUpdate('cascade');
            $table->unsignedBigInteger('application_id')->nullable();
            $table->foreign('application_id')->references('id')->on('applications')->onDelete('cascade')->onUpdate('cascade');
            $table->string('advance_payment_amount');
            $table->string('status')->default('active');
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
        Schema::dropIfExists('advance_payment_cases');
    }
}
