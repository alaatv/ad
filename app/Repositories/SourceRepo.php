<?php


namespace App\Repositories;


use Carbon\Carbon;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\DB;

class SourceRepo
{
    /**
     * @param array $sourceNames
     * @param int $customerId
     * @return Builder
     */
    public static function getValidSourceViaUser(array $sourceNames , int $customerId): Builder{
        return DB::table('sources')
            ->join('contracts', 'sources.id', '=', 'contracts.source_id')
            ->whereIn('sources.name' , $sourceNames)
            ->where('sources.enable' , 1)
            ->where('contracts.user_id' , $customerId)
            ->where(function (Builder $q){
                $q ->where('contracts.since', '<=', Carbon::createFromFormat('Y-m-d H:i:s', Carbon::now('Asia/Tehran')))
                    ->orWhereNull('contracts.since');
            })->where(function (Builder $q) {
                $q->where('contracts.till', '>=', Carbon::createFromFormat('Y-m-d H:i:s', Carbon::now('Asia/Tehran')))
                    ->orWhereNull('contracts.till');
            })
            ->select('*');
    }

    /**
     * @param int $customerId
     * @return Builder
     */
    public static function getValidSourceViaContract(int $customerId): Builder
    {
        return DB::table('contracts')
            ->join('sources', 'sources.id', '=', 'contracts.source_id')
            ->where('sources.enable', 1)
            ->where('contracts.user_id', $customerId)
            ->where(function (Builder $q) {
                $q->where('contracts.since', '<=', Carbon::createFromFormat('Y-m-d H:i:s', Carbon::now('Asia/Tehran')))
                    ->orWhereNull('contracts.since');
            })->where(function (Builder $q) {
                $q->where('contracts.till', '>=', Carbon::createFromFormat('Y-m-d H:i:s', Carbon::now('Asia/Tehran')))
                    ->orWhereNull('contracts.till');
            })
            ->select('sources.*');
    }
}
