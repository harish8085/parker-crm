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
        Schema::table('bank_mis', function (Blueprint $table) {
            if (!Schema::hasColumn('bank_mis', 'company_name')) {
                $table->string('company_name')->nullable()->after('product_id');
            }
        });

        Schema::table('contest_mis', function (Blueprint $table) {
            if (!Schema::hasColumn('contest_mis', 'company_name')) {
                $table->string('company_name')->nullable()->after('bank_id');
            }
        });

        Schema::table('insurance_mis', function (Blueprint $table) {
            if (!Schema::hasColumn('insurance_mis', 'company_name')) {
                $table->string('company_name')->nullable()->after('bank_id');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('bank_mis', function (Blueprint $table) {
            if (Schema::hasColumn('bank_mis', 'company_name')) {
                $table->dropColumn('company_name');
            }
        });

        Schema::table('contest_mis', function (Blueprint $table) {
            if (Schema::hasColumn('contest_mis', 'company_name')) {
                $table->dropColumn('company_name');
            }
        });

        Schema::table('insurance_mis', function (Blueprint $table) {
            if (Schema::hasColumn('insurance_mis', 'company_name')) {
                $table->dropColumn('company_name');
            }
        });
    }
};
