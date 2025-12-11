<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddNewColumnsInAnnouncements extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('announcements', function (Blueprint $table) {
            $table->unsignedBigInteger('bank_id')->nullable()->after('message_attachment');
            $table->unsignedBigInteger('product_id')->nullable()->after('bank_id');
            $table->unsignedBigInteger('announcement_category_id')->nullable()->after('product_id');
            $table->foreign('bank_id')->references('id')->on('banks')->onDelete('cascade')->onUpdate('cascade');
            $table->foreign('product_id')->references('id')->on('products')->onDelete('cascade')->onUpdate('cascade');
            $table->foreign('announcement_category_id')->references('id')->on('announcement_categories')->onDelete('cascade')->onUpdate('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('announcements', function (Blueprint $table) {
            $table->dropForeign(['bank_id']);
            $table->dropForeign(['product_id']);
            $table->dropForeign(['announcement_category_id']);
            $table->dropColumn(['bank_id', 'product_id', 'announcement_category_id']);
        });
    }
}
