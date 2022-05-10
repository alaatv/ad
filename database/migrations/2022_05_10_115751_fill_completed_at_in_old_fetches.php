<?php

use App\Repositories\Repo;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class FillCompletedAtInOldFetches extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('fetches', function (Blueprint $table) {
            $fetches = Repo::getRecords('fetches', ['*'])->get();
            foreach ($fetches as $fetch) {
                DB::table('fetches')->where('id', $fetch->id)->update([
                    'completed_at' => $fetch->updated_at,
                ]);
            }
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
            $fetches = Repo::getRecords('fetches', ['*'])->get();
            foreach ($fetches as $fetch) {
                DB::table('fetches')->where('id', $fetch->id)->update([
                    'completed_at' => null,
                ]);
            }
        });
    }
}
