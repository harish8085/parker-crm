<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateDsaCodesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('dsa_codes', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('product_id');
            $table->string('group')->nullable(); // Group identifier
            $table->string('code')->nullable(); // String field for DSA code
            $table->timestamps();
            // Foreign key constraint
            $table->foreign('product_id')->references('id')->on('bank_products')
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
        Schema::dropIfExists('dsa_codes');
    }
}
