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
        Schema::table('settlements', function (Blueprint $table) {
            $table->string('settlement_type')->default('commission')->after('application_id');
            $table->index(['user_id', 'settlement_type', 'status'], 'idx_settlements_user_type_status');
        });

        Schema::table('settlement_distributions', function (Blueprint $table) {
            $table->string('settlement_type')->default('commission')->after('application_id');
            $table->unsignedBigInteger('contest_mis_id')->nullable()->after('application_id');
            $table->index(['settlement_type', 'contest_mis_id'], 'idx_dist_type_contest');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('settlement_distributions', function (Blueprint $table) {
            $table->dropIndex('idx_dist_type_contest');
            $table->dropColumn(['settlement_type', 'contest_mis_id']);
        });

        Schema::table('settlements', function (Blueprint $table) {
            $table->dropIndex('idx_settlements_user_type_status');
            $table->dropColumn('settlement_type');
        });
    }
};

