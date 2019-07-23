<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateContractpagesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('contractpages', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('conttract_id')->comment('آی دی قرارداد');
            $table->string('url')->nullable()->comment('آدرس صفحه تبلیغ مشمول قرارداد');
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('conttract_id')
                ->references('id')
                ->on('contracts')
                ->onDelete('cascade')
                ->onupdate('cascade');

        });

        DB::statement("ALTER TABLE `contractpages` comment 'قرارداد های تبلیغاتی'");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('contractpages');
    }
}
