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
     * @param array $multiValueFilter
     * @return Builder
     */
    public static function getRecords(string $table, array $columns = [], array $filters = [], array $multiValueFilter = []): Builder
    {
        if (empty($columns) || in_array('*', $columns))
            $columns = '*';

        $records = DB::table($table)->select($columns);

        self::filter($records, $filters);
        self::filterMultipleValue($records, $multiValueFilter);

        return $records;
    }

    public static function insertRecord(string $table, array $data): bool
    {
        return DB::table($table)->insert($data);
    }

    public static function updateRecord(string $table, int $id, array $data): bool
    {
        return DB::table($table)->where('id', $id)->update($data);
    }

    private static function filter(Builder $records, array $filters): void
    {
        foreach ($filters as $key => $filter) {
            $records->where($key, $filter);
        }
    }

    private static function filterMultipleValue(Builder $records, array $filters): void
    {
        foreach ($filters as $key => $filter) {
            $records->whereIn($key, $filter);
        }
    }
}
