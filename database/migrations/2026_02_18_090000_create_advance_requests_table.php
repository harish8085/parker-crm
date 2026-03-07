<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAdvanceRequestsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('advance_requests', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id')->comment('Channel partner user id');
            $table->unsignedBigInteger('requested_by')->comment('Checker user id');
            $table->string('case_type')->default('no_case');
            $table->decimal('requested_amount', 15, 2);
            $table->text('advance_remark')->nullable();
            $table->string('status')->default('pending'); // pending, approved, rejected
            $table->unsignedBigInteger('admin_action_by')->nullable();
            $table->timestamp('admin_action_at')->nullable();
            $table->text('admin_remark')->nullable();
            $table->unsignedInteger('approved_log_id')->nullable();
            $table->timestamps();

            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade')->onUpdate('cascade');
            $table->foreign('requested_by')->references('id')->on('users')->onDelete('cascade')->onUpdate('cascade');
            $table->foreign('admin_action_by')->references('id')->on('users')->onDelete('set null')->onUpdate('cascade');
            $table->foreign('approved_log_id')->references('id')->on('advance_amount_logs')->onDelete('set null')->onUpdate('cascade');

            $table->index(['status', 'created_at']);
            $table->index('user_id');
            $table->index('requested_by');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('advance_requests');
    }
}

