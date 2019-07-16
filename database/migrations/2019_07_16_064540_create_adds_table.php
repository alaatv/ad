<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateAddsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('adds', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('name')->nullable()->comment('نام تبلیغ');
            $table->string('image')->nullable()->comment('مسیر عکس تبلیغ');
            $table->string('link')->nullable()->comment('لینک تبلیغ');
            $table->timestamps();
            $table->softDeletes();
        });

        DB::statement("ALTER TABLE `adds` comment 'تبلیغ ها'");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('adds');
    }
}
