<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSettlementDistributionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('settlement_distributions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('settlement_id');
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('bank_account_id');
            $table->decimal('amount', 10, 2);
            $table->decimal('tds', 10, 2);
            $table->string('utr_number')->nullable();
            $table->string('payment_status')->nullable();
            $table->string('rejection_reason')->nullable();
            $table->string('file_name')->nullable();
            $table->timestamps();

            // Define foreign key constraints
            $table->foreign('settlement_id')->references('id')->on('settlements')->onDelete('cascade');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('bank_account_id')->references('id')->on('bank_data')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('settlement_distributions');
    }
}
