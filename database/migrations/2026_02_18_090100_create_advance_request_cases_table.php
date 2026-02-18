<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAdvanceRequestCasesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('advance_request_cases', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('advance_request_id');
            $table->unsignedBigInteger('application_id');
            $table->string('product')->nullable();
            $table->string('product_percent')->nullable();
            $table->decimal('advance_payment_amount', 15, 2);
            $table->timestamps();

            $table->foreign('advance_request_id')->references('id')->on('advance_requests')->onDelete('cascade')->onUpdate('cascade');
            $table->foreign('application_id')->references('id')->on('applications')->onDelete('cascade')->onUpdate('cascade');

            $table->index('advance_request_id');
            $table->index('application_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('advance_request_cases');
    }
}

