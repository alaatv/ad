<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateLogsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('logs', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('category_id')->nullable()->comment('آی دی دسته این لاگ');
            $table->unsignedInteger('source_id')->nullable()->comment('آی دی منبع تبلیغ این لاگ');
            $table->string('text')->nullable()->comment('متن لاگ');
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('category_id')
                ->references('id')
                ->on('logcategories')
                ->onDelete('cascade')
                ->onupdate('cascade');

            $table->foreign('source_id')
                ->references('id')
                ->on('sources')
                ->onDelete('cascade')
                ->onupdate('cascade');
        });
        DB::statement("ALTER TABLE `logs` comment 'لاگ ها'");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('logs');
    }
}
