<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('contest_mis', function (Blueprint $table) {
            $table->string('status')->default('pending')->after('payment_status');
            $table->decimal('sharing_contest_commission', 5, 2)->default(50.00)->after('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('contest_mis', function (Blueprint $table) {
            $table->dropColumn(['status', 'sharing_contest_commission']);
        });
    }
};

