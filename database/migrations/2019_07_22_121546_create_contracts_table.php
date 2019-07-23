<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateContractsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('contracts', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('user_id')->comment('آیدی مشتری');
            $table->unsignedInteger('source_id')->comment('آیدی منبع تبلیغ');
            $table->timestamp('since')->nullable()->comment('شروع مدت قرارداد');
            $table->timestamp('till')->nullable()->comment('پایان مدت قرارداد');
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('user_id')
                ->references('id')
                ->on('users')
                ->onDelete('cascade')
                ->onupdate('cascade');

            $table->foreign('source_id')
                ->references('id')
                ->on('sources')
                ->onDelete('cascade')
                ->onupdate('cascade');
        });

        DB::statement("ALTER TABLE `contracts` comment 'قرارداد های تبلیغاتی'");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('contracts');
    }
}
