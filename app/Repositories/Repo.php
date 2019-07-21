<?php


namespace App\Repositories;


use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\DB;

class Repo
{
    protected static $table;

    /**
     * @param string $table
     * @param array $columns
     * @param array $filters
     * @return Builder
     */
    public static function getRecords(string $table, array $columns , array $filters):Builder{
        if(in_array('*' , $columns))
            $columns = '*';

        $records = DB::table($table)->select($columns);

        self::filter($records, $filters);

        return $records;
    }

    public static function insertRecord(string $table, array $data ){
        return DB::table($table)->insert($data);
    }

    private static function filter(Builder $records , array $filters){
        foreach ($filters as $key => $filter) {
            $records->where($key , $filter);
        }
    }
}
