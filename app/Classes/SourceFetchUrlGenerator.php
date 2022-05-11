<?php


namespace App\Classes;


use App\Repositories\Repo;
use Carbon\Carbon;

class SourceFetchUrlGenerator
{
    private $source;
    private $since;
    private $till;

    /**
     * SourceFetchUrlGenerator constructor.
     * @param $source
     */
    public function __construct($source, $since, $till)
    {
        $this->source = $source;
        $this->since = $since;
        $this->till = $till;
    }

    /**
     * @return string|null
     */
    public function generateUrl(): ?string
    {
        $lastFetch = Repo::getRecords('fetches', ['*'], ['source_id' => $this->source->id, 'completed_at' => null])
            ->orderByDesc('created_at')->first();
        $sourceFetchUrl = $this->source->fetch_url;
        if (stripos($sourceFetchUrl, '?') === false) {
            $sourceFetchUrl .= '?';
        }

        //TODO: make sure if we can pass since and till dates to query string
        if (is_null($lastFetch)) {
            return $sourceFetchUrl . '&timestamp=' . Carbon::parse($this->since)->timestamp;
        }

        if ($lastFetch->current_page < $lastFetch->last_page) {
            return $lastFetch->next_page_url;
        }

        return $sourceFetchUrl . '&timestamp=' . Carbon::parse($lastFetch->completed_at)->timestamp;
    }
}
