<?php


namespace App\Jobs;

use App\Classes\AdFetcher;
use App\Classes\AdItemInserter;
use App\Classes\AdPicTransferrer;
use App\Classes\SourceFetchUrlGenerator;
use App\Repositories\Repo;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use PHPUnit\Framework\Exception;
use stdClass;

class FetchAd extends Job
{
    /** @var AdFetcher $adFetcher */
    private AdFetcher $adFetcher;
    /** @var AdItemInserter $adItemInserter */
    private AdItemInserter $adItemInserter;
    /** @var AdPicTransferrer $adPicTransferrer */
    private AdPicTransferrer $adPicTransferrer;
    private string $sourceName;
    private string $since;
    private null|object $source;
    private null|object $lastFetch;

    /**
     * Create a new job instance.
     *
     * @param string $sourceName
     * @param string $since
     * @param AdFetcher $adFetcher
     * @param AdItemInserter $adItemInserter
     * @param AdPicTransferrer $adPicTransferrer
     */
    public function __construct(string $sourceName, string $since, AdFetcher $adFetcher, AdItemInserter $adItemInserter, AdPicTransferrer $adPicTransferrer)
    {
        $this->sourceName = $sourceName;
        $this->since = $since;
        $this->source = Repo::getRecords('sources', ['*'], ['name' => $this->sourceName])->first();
        $this->lastFetch = Repo::getRecords('fetches', ['*'], ['source_id' => $this->source->id, 'completed_at' => null])
            ->orderByDesc('created_at')->first();
        $this->adFetcher = $adFetcher;
        $this->adItemInserter = $adItemInserter;
        $this->adPicTransferrer = $adPicTransferrer;
    }

    /**
     * Execute the console command.
     */
    public function handle()
    {
        /** @var stdClass $source */
        if (isset($this->source)) {
            [$donePages, $failedPages] = $this->fetch();
        } else {
            Log::error('source is not set with name: ' . $this->sourceName);
            throw new Exception('source is not set with name: ' . $this->sourceName);
        }
    }

    /**
     * @return array
     */
    private function fetch(): array
    {
        $row = 1;
        $sleepCount = 0;
        $fetchUrl = (new SourceFetchUrlGenerator($this->source, $this->since))->generateUrl();
        $urls = [];
        $failedPages = 0;
        $donePages = 0;

        if (is_null($fetchUrl)) {
            return [0, 0];
        }

        do {
            [$fetchDone, $items, $currentPage, $nextPageUrl, $lastPage, $resultText] = $this->adFetcher->fetchAd($fetchUrl);
            if (!$fetchDone && $row == 1) {
                sleep(300);
                $sleepCount++;
                if ($sleepCount == 3) {
                    Log::error('response status code is not 200 for page ' . $currentPage . ' | request url: ' . $fetchUrl);
                    throw new Exception('response status code is not 200 for page ' . $currentPage . ' | request url: ' . $fetchUrl);
                }
                continue;
            }
            if ($row == 1) {
                for ($i = 1; $i <= $lastPage; $i++) {
                    $urls[] = $this->source->fetch_url . '&page=' . $i;
                }
            }
            if (!$fetchDone) {
                sleep(300);
                $sleepCount++;
                if ($sleepCount == 3) {
                    $sleepCount = 0;
                    $failedPages++;
                    Log::error('response status code is not 200 for page ' . $currentPage . ' | request url: ' . $fetchUrl);
                    $fetchUrl = $urls[$currentPage + 1];
                    continue;
                }
                continue;
            }
            if (empty($items) && isset($nextPageUrl)) {
                $fetchUrl = $nextPageUrl;
                Log::error('response data is null for page ' . $currentPage . ' | request url: ' . $fetchUrl);
                $failedPages++;
                continue;
            }
            if (empty($items) && !isset($lastPage)) {
                Log::error('response data is null for last page : ' . $currentPage . ' | request url: ' . $fetchUrl);
                $failedPages++;
                break;
            }
            $this->storeItems($this->source, $items);

            if ($currentPage === 1) {
                $this->insertFetch($this->source->id, $currentPage, $lastPage, $nextPageUrl);
                $this->lastFetch = DB::table('fetches')->where('source_id', $this->source->id)
                    ->latest()->first();
            } else {
                $this->updateFetch($this->source->id, $currentPage, $nextPageUrl, $this->lastFetch->id);
            }

            $fetchUrl = $nextPageUrl;
            $donePages++;
            if ($currentPage === $this->lastFetch->last_page) {
                Repo::updateRecord('fetches', $this->lastFetch->id, [
                    'completed_at' => Carbon::now(),
                ]);
            }
            $row++;
        } while ($currentPage < $lastPage);

        return [$donePages, $failedPages];
    }

    /**
     * @param int $sourceID
     * @param int $currentPage
     * @param int|null $lastPage
     * @param string|null $nextPageUrl
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
     * @param $fetch_id
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
     */
    private function storeItems(stdClass $source, array $items): void
    {
        foreach ($items as $item) {
            $this->adItemInserter->storeOrUpdateItem($source, $item, $this->adPicTransferrer);
        }
    }
}
