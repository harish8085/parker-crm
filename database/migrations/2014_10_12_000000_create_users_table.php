<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('Emp_Id');
            $table->string('first_name');
            $table->string('last_name')->nullable();
            $table->string('email')->unique();
            $table->string('phone')->unique();
            $table->string('state');
            $table->string('district');
            $table->string('pan_number')->unique();
            $table->string('aadhar_number')->unique();
            $table->boolean('status')->default(1); // Add the role column with a default value of 'user'
            $table->string('service_type')->nullable();
            $table->string('address_1')->nullable();
            $table->string('address_2')->nullable();
            $table->string('landmark')->nullable();
            $table->string('pincode')->nullable();
            $table->string('password');
            $table->enum('user_type', ['admin','channel', 'sales', 'staff'])->default('staff');
            $table->rememberToken();
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
        Schema::dropIfExists('users');
    }
}
