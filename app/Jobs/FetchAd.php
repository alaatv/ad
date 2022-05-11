<?php


namespace App\Jobs;

use App\Classes\AdFetcher;
use App\Classes\AdItemInserter;
use App\Classes\AdPicTransferrer;
use App\Classes\SourceFetchUrlGenerator;
use App\Repositories\Repo;
use Carbon\Carbon;
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
    private $till;

    /**
     * Create a new job instance.
     *
     * @param string $sourceName
     */
    public function __construct(string $sourceName, string $since, string $till)
    {
        $this->sourceName = $sourceName;
        $this->since = $since;
        $this->till = $till;
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
        $source = Repo::getRecords('sources', ['*'], ['name' => $this->sourceName])->first();
        if (isset($source)) {
            [$donePages, $failedPages] = $this->fetch($source, $this->since, $this->till);
        }
    }

    /**
     * @param stdClass $source
     * @return array
     */
    private function fetch(stdClass $source, $since, $till): array
    {
        $fetchUrl = (new SourceFetchUrlGenerator($source, $since, $till))->generateUrl();

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

            $this->insertOrUpdateFetch($source, $currentPage, $lastPage, $nextPageUrl);
            $fetchUrl = $nextPageUrl;
            $donePages++;
        } while ($currentPage < $lastPage);

        return [$donePages, $failedPages];
    }

    /**
     * @param stdClass $source
     * @param $currentPage
     * @param $lastPage
     * @param $nextPageUrl
     * @return bool
     */
    private function insertOrUpdateFetch(stdClass $source, $currentPage, $lastPage, $nextPageUrl): bool
    {
        if ($currentPage == 1) {
            return $this->insertFetch($source->id, $currentPage, $lastPage, $nextPageUrl);
        }

        return $this->updateFetch($source->id, $currentPage, $nextPageUrl);
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
    private function updateFetch(int $sourceID, $currentPage, $nextPageUrl): bool
    {
        return Repo::updateRecord('fetches', [
            'source_id' => $sourceID,
            'current_page' => $currentPage,
            'next_page_url' => $nextPageUrl,
            'updated_at' => Carbon::now(),
            'completed_at' => Carbon::now(),
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
