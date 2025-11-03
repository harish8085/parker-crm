<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateBankProductsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('bank_products', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('bank_id');
            $table->foreignId('product_id')->constrained('products')->onDelete('cascade'); // foreign key column
            $table->boolean('auto_generate_lan')->default(0); // foreign key column
            $table->timestamps();
            $table->foreign('bank_id')->references('id')->on('banks')
                ->onDelete('cascade');  // Ensures that bank products are deleted when a bank is deleted
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('bank_products');
    }
}
