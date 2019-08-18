<?php


namespace App\Classes;


use App\Repositories\Repo;
use Carbon\Carbon;

class SourceFetchUrlGenerator
{
    const FIRST_FETCH_DATE = '2016-03-01';

    private $source;

    /**
     * SourceFetchUrlGenerator constructor.
     * @param $source
     */
    public function __construct($source)
    {
        $this->source = $source;
    }

    /**
     * @return string|null
     */
    public function generateUrl():?string
    {
        $lastFetch = Repo::getRecords('fetches', ['*'], ['source_id' => $this->source->id])->orderByDesc('created_at')->first();

        if(stripos($lastFetch , '?') === false){
            $lastFetch .= '?';
        }

        if (is_null($lastFetch)) {
            return $this->source->fetch_url.'&timestamp='.Carbon::parse(self::FIRST_FETCH_DATE)->timestamp;
        }

        if ($lastFetch->current_page < $lastFetch->last_page) {
            return  $lastFetch->next_page_url;
        }

        return $this->source->fetch_url . '&timestamp=' . Carbon::parse($lastFetch->updated_at)->timestamp;
    }
}
