<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('users', function (Blueprint $table) {
            $table->increments('id');
            $table->string('UUID')->unique()->comment('کد مشتری');
            $table->string('name')->nullable()->comment('نام مشتری');
            $table->string('email')->nullable()->comment('ایمیل مشتری');
            $table->string('password')->comment('رمز عبور');
            $table->string('website')->nullable()->comment('وبسایت مشتری');
            $table->string('address')->nullable()->comment('آدرس مشتری');
            $table->timestamps();
            $table->softDeletes();
        });

        DB::statement("ALTER TABLE `users` comment 'مشتریانی که از سرویس تبلیغ استفاده می کنند'");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('users');
    }
}
