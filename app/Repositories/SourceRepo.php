<?php


namespace App\Repositories;


use Carbon\Carbon;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\DB;

class SourceRepo
{
    /**
     * @param $sourcesName
     * @param $customerId
     * @return Builder
     */
    public static function getValidSourceViaUser($sourcesName , $customerId): Builder{
        return DB::table('sources')
            ->join('contracts', 'sources.id', '=', 'contracts.source_id')
            ->whereIn('sources.name' , $sourcesName)
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
     * @param $customer
     * @return Builder
     */
    public static function getValidSourceViaContract($customer): Builder
    {
        return DB::table('contracts')
            ->join('sources', 'sources.id', '=', 'contracts.source_id')
            ->where('sources.enable', 1)
            ->where('contracts.user_id', $customer->id)
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
