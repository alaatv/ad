<?php


namespace App\Classes;


use App\Repositories\Repo;
use Carbon\Carbon;

class SourceFetchUrlGenerator
{
    private $source;
    private $since;

    /**
     * SourceFetchUrlGenerator constructor.
     * @param $source
     */
    public function __construct($source, $since)
    {
        $this->source = $source;
        $this->since = $since;
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

        if (is_null($lastFetch)) {
            return $sourceFetchUrl . '&timestamp=' . Carbon::parse($this->since)->timestamp;
        }

        if ($lastFetch->current_page < $lastFetch->last_page) {
            return $lastFetch->next_page_url;
        }

        return $sourceFetchUrl . '&timestamp=' . Carbon::parse($lastFetch->updated_at)->timestamp;
    }
}
