<?php


namespace App\Repositories;


use Carbon\Carbon;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\DB;

class Repo
{
    protected static $table;

    /**
     * @param string $table
     * @param array $columns
     * @param array $filters
     * @param array $multiValueFilter
     * @return Builder
     */
    public static function getRecords(string $table, array $columns=[] , array $filters=[] , array $multiValueFilter=[]):Builder{
        if(empty($columns) || in_array('*' , $columns))
            $columns = '*';

        $records = DB::table($table)->select($columns);

        self::filter($records, $filters);
        self::filterMultipleValue($records, $multiValueFilter);

        return $records;
    }

    public static function insertRecord(string $table, array $data ){
        return DB::table($table)->insert($data);
    }

    public static function valid(Builder $query):Builder{
        return $query->where(function (Builder $q){
            $q ->where('since', '<=', Carbon::createFromFormat('Y-m-d H:i:s', Carbon::now('Asia/Tehran')))
                ->orWhereNull('since');
        })->where(function (Builder $q){
            $q ->where('till', '>=', Carbon::createFromFormat('Y-m-d H:i:s', Carbon::now('Asia/Tehran')))
                ->orWhereNull('till');
        });
    }

    public static function enable(Builder $query) :Builder
    {
        return $query->where('enable' , 1);
    }

    private static function filter(Builder $records , array $filters){
        foreach ($filters as $key => $filter) {
            $records->where($key , $filter);
        }
    }

    private static function filterMultipleValue(Builder $records , array $filters){
        foreach ($filters as $key => $filter) {
            $records->whereIn($key , $filter);
        }
    }
}
