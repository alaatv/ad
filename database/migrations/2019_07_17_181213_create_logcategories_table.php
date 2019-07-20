<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateLogcategoriesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('logcategories', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name')->nullable()->comment('نام دسته');
            $table->string('display_name')->nullable()->comment('نام قابل نمایش دسته');
            $table->string('description')->nullable()->comment('توضیح دسته');
            $table->timestamps();
            $table->softDeletes();
        });
        DB::statement("ALTER TABLE `logcategories` comment 'دسته بندی لاگ ها'");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('logcategories');
    }
}
