<?php


namespace App\Jobs;

use App\Classes\AdFetcher;
use App\Classes\AdItemInserter;
use App\Classes\AdPicTransferrer;
use App\Classes\SourceFetchUrlGenerator;
use App\Repositories\Repo;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use stdClass;

class FetchAd extends Job
{
    /** @var AdFetcher $adFetcher */
    private $adFetcher;
    /** @var AdItemInserter $adItemInserter */
    private $adItemInserter;
    /** @var AdPicTransferrer $adPicTransferrer */
    private $adPicTransferrer;
    private $sourceName;
    private $since;
    private $source;
    private $lastFetch;

    /**
     * Create a new job instance.
     *
     * @param string $sourceName
     */
    public function __construct(string $sourceName, string $since)
    {
        $this->sourceName = $sourceName;
        $this->since = $since;
        $this->source = Repo::getRecords('sources', ['*'], ['name' => $this->sourceName])->first();
        $this->lastFetch = Repo::getRecords('fetches', ['*'], ['source_id' => $this->source->id, 'completed_at' => null])
            ->orderByDesc('created_at')->first();
    }

    /**
     * Execute the console command.
     * @param AdFetcher $adFetcher
     * @param AdItemInserter $adItemInserter
     * @param AdPicTransferrer $adPicTransferrer
     */
    public function handle(AdFetcher $adFetcher, AdItemInserter $adItemInserter, AdPicTransferrer $adPicTransferrer)
    {
        $this->adFetcher = $adFetcher;
        $this->adItemInserter = $adItemInserter;
        $this->adPicTransferrer = $adPicTransferrer;

        /** @var stdClass $source */
        if (isset($this->source)) {
            [$donePages, $failedPages] = $this->fetch($this->source, $this->since);
        }
    }

    /**
     * @param stdClass $source
     * @return array
     */
    private function fetch(stdClass $source, $since): array
    {
        $fetchUrl = (new SourceFetchUrlGenerator($source, $since))->generateUrl();

        if (is_null($fetchUrl)) {
            return [0, 0];
        }

        $failedPages = 0;
        $donePages = 0;

        do {
            $counter = 0;
            [$fetchDone, $items, $currentPage, $nextPageUrl, $lastPage, $resultText] = $this->adFetcher->fetchAd($fetchUrl);
            if (!$fetchDone) {
                $failedPages++;
                continue;
            }

            if (empty($items)) {
                continue;
            }
            $this->storeItems($source, $items, $counter);

            if ($currentPage == 1) {
                $this->insertFetch($source->id, $currentPage, $lastPage, $nextPageUrl);
                $this->lastFetch = DB::table('fetches')->where('source_id', $source->id)
                    ->latest()->first();
            } else {
                $this->updateFetch($source->id, $currentPage, $nextPageUrl, $this->lastFetch->id);
            }

            $fetchUrl = $nextPageUrl;
            $donePages++;
            if ($currentPage == $this->lastFetch->last_page) {
                Repo::updateRecord('fetches', $this->lastFetch->id, [
                    'completed_at' => Carbon::now(),
                ]);
            }
        } while ($currentPage < $lastPage);

        return [$donePages, $failedPages];
    }

    /**
     * @param int $sourceID
     * @param int $currentPage
     * @param int $lastPage
     * @param string $nextPageUrl
     * @return bool
     */
    private function insertFetch(int $sourceID, int $currentPage, int $lastPage = null, string $nextPageUrl = null): bool
    {
        return Repo::insertRecord('fetches', [
            'source_id' => $sourceID,
            'current_page' => $currentPage,
            'last_page' => $lastPage,
            'next_page_url' => $nextPageUrl,
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
        ]);
    }

    /**
     * @param int $sourceID
     * @param $currentPage
     * @param $nextPageUrl
     * @return bool
     */
    private function updateFetch(int $sourceID, $currentPage, $nextPageUrl, $fetch_id): bool
    {
        return Repo::updateRecord('fetches', $fetch_id, [
            'source_id' => $sourceID,
            'current_page' => $currentPage,
            'next_page_url' => $nextPageUrl,
            'updated_at' => Carbon::now(),
        ]);
    }

    /**
     * @param stdClass $source
     * @param array $items
     * @param int $counter
     */
    private function storeItems(stdClass $source, array $items, int $counter): void
    {
        foreach ($items as $key => $item) {
            if ($this->adItemInserter->storeOrUpdateItem($source, $item, $this->adPicTransferrer)) {
                $counter++;
            }
        }
    }
}
