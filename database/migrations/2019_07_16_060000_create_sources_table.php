<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateSourcesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('sources', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name')->nullable()->comment('نام منبع');
            $table->string('display_name')->nullable()->comment('نام قابل نمایش منبع');
            $table->string('description')->nullable()->comment('توصیج منبع');
            $table->string('fetch_url')->nullable()->comment('آدرس برای فچ کردن محتواهای این سایت');
            $table->tinyInteger('enable')->default(1)->comment('فعال یا غیرفعال');
            $table->timestamps();
            $table->softDeletes();
        });
        DB::statement("ALTER TABLE `sources` comment 'منبع های تبلیغ'");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('sources');
    }
}
