<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddCompletedAtToFetches extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('fetches', function (Blueprint $table) {
            $table->timestamp('completed_at')->nullable()->after('updated_at')->comment('تاریخ اتمام فچ');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('fetches', function (Blueprint $table) {
            $table->dropColumn('completed_at');
        });
    }
}
