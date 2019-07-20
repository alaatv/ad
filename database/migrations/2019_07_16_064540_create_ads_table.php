<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateAdsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('ads', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('source_id')->nullable()->comment('منبع تبلیغ');
            $table->string('name')->nullable()->comment('نام تبلیغ');
            $table->string('image')->nullable()->comment('مسیر عکس تبلیغ');
            $table->string('link')->nullable()->comment('لینک تبلیغ');
            $table->tinyInteger('enable')->default(1)->comment('فعال یا غیرفعال');
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('source_id')
                ->references('id')
                ->on('sources')
                ->onDelete('cascade')
                ->onupdate('cascade');
        });

        DB::statement("ALTER TABLE `ads` comment 'تبلیغ ها'");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('ads');
    }
}
