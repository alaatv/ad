<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateFetchesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('fetches', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('source_id')->nullable()->comment('آی دی منبع فچ');
            $table->integer('first_item_id')->nullable()->comment('آی دی اولین آیتم فچ شده');
            $table->integer('page')->nullable()->comment('شماره صفحه ای که فچ شده');
            $table->integer('per_page')->nullable()->comment('تعداد آیتم های فچ شده در هر صفحه');
            $table->integer('fetched')->nullable()->comment('تعداد فچ شده');
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('source_id')
                ->references('id')
                ->on('sources')
                ->onDelete('cascade')
                ->onupdate('cascade');
        });
        DB::statement("ALTER TABLE `fetches` comment 'فچ های انجام شده از منبع'");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('fetches');
    }
}
