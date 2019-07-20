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
     * @param array $parameters
     * @return Builder
     */
    public static function getRecords(string $table, array $columns , array $parameters):Builder{
        if(in_array('*' , $columns))
            $columns = '*';

        $records = DB::table($table)->select($columns);

        self::filter($records, $parameters);

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
