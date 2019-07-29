<?php


namespace App\Jobs;

use App\Classes\AdFetcher;
use App\Classes\AdItemInserter;
use App\Classes\AdPicTransferrer;
use App\Repositories\Repo;
use Carbon\Carbon;
use stdClass;
use Symfony\Component\Console\Helper\ProgressBar;

class FetchAd extends Job
{
    private $adFetcher;
    private $adItemInserter;
    private $adPicTransferrer;
    private $sourceName;

    /**
     * Create a new job instance.
     *
     * @param string $sourceName
     */
    public function __construct(string $sourceName)
    {
        $this->sourceName = $sourceName;
    }

    /**
     * Execute the console command.
     * @param AdFetcher $adFetcher
     * @param AdItemInserter $adItemInserter
     * @param AdPicTransferrer $adPicTransferrer
     * @param string $sourceName
     */
    public function handle(AdFetcher $adFetcher , AdItemInserter $adItemInserter , AdPicTransferrer $adPicTransferrer)
    {
        $this->adFetcher = $adFetcher;
        $this->adItemInserter=$adItemInserter;
        $this->adPicTransferrer=$adPicTransferrer;

        /** @var stdClass $source */
        $source = Repo::getRecords('sources' , ['*'], ['name'=>$this->sourceName])->first();
        if(isset($source)){
            [$donePages, $failedPages] = $this->fetch($source);
//            $this->printInfo(['total fetched pages: '.$donePages,'total failed pages: '.$failedPages]);
        }else{
//            $this->printInfo(['Source not found']);
        }
    }

    /**
     * @param stdClass $source
     * @return array
     */
    private function fetch(stdClass $source): array
    {
        $fetchUrl = $this->adFetcher->getFetchUrl($source);
        if(is_null($fetchUrl)) {
//            $this->printInfo(['Fetch Url not found']);
            return [0, 0];
        }

//        $this->printInfo(['Start fetching...']);
        $failedPages = 0;
        $donePages = 0;

        do {
            $counter = 0;
//            $this->printInfo(["Fetching $fetchUrl"]);
            [$fetchDone , $items , $currentPage , $nextPageUrl , $lastPage, $resultText] = $this->adFetcher->fetchAd($fetchUrl);
            if(!$fetchDone){
//                $this->printInfo(["Failed on fetching $fetchUrl","response: $resultText"]);
                $failedPages++;
                continue;
            }

            if (empty($items)) {
//                $this->printInfo(["No items fetched in request for page $currentPage"]);
                continue;
            }

//            $this->printInfo(['Inserting '.count($items).' items']);
//            $bar = $this->output->createProgressBar(count($items));
            $this->storeItems($source, $items, $counter);
//            $bar->finish();
//            $bar->setProgress($counter);
//            $this->info("\n");

            $this->insertOrUpdateFetch($source, $currentPage, $lastPage, $nextPageUrl);
            $fetchUrl = $nextPageUrl;
            $donePages++;
        } while ($currentPage < $lastPage );

        return [$donePages, $failedPages];
    }

    /**
     * @param array $texts
     */
    private function printInfo(array $texts):void
    {
        foreach ($texts as $text) {
            $this->info($text);
            $this->info("\n");
        }
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
    private function insertFetch(int $sourceID, int $currentPage, int $lastPage=null, string $nextPageUrl=null):bool
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
    private function updateFetch(int $sourceID, $currentPage, $nextPageUrl):bool
    {
        return Repo::updateRecord('fetches', [
            'source_id' => $sourceID,
            'current_page' => $currentPage,
            'next_page_url' => $nextPageUrl,
            'updated_at' => Carbon::now(),
        ]);
    }

    /**
     * @param stdClass $source
     * @param array $items
     * @param ProgressBar $bar
     * @param int $counter
     */
    private function storeItems(stdClass $source, array $items, int $counter): void
    {
        foreach ($items as $key => $item) {
            if ($this->adItemInserter->storeItem($source, $item, $this->adPicTransferrer)) {
//                $bar->advance();
                $counter++;
            }
        }
    }
}
