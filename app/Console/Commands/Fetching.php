<?php

namespace App\Console\Commands;

use App\Classes\AdFetcher;
use App\Classes\AdItemInserter;
use App\Classes\AdPicTransferrer;
use App\Repositories\Repo;
use Carbon\Carbon;
use Illuminate\Console\Command;
use stdClass;

class Fetching extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'adengine:fetching {source : name of the source to be fetched}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Fetiching ads from a source';

    private $adFetcher;
    private $adItemInserter;
    private $adPicTransferrer;

    /**
     * Create a new command instance.
     *
     * @param AdFetcher $adFetcher
     * @param AdItemInserter $adItemInserter
     * @param AdPicTransferrer $adPicTransferrer
     */
    public function __construct(AdFetcher $adFetcher , AdItemInserter $adItemInserter , AdPicTransferrer $adPicTransferrer)
    {
        parent::__construct();
        $this->adFetcher = $adFetcher;
        $this->adItemInserter=$adItemInserter;
        $this->adPicTransferrer=$adPicTransferrer;
    }

    /**
     * Execute the console command.
     *
     * @param AdFetcher $adFetcher
     * @param AdItemInserter $adItemInserter
     * @param AdPicTransferrer $adPicTransferrer
     * @return mixed
     */
    public function handle()
    {
        $sourceName = $this->argument('source');

        /** @var stdClass $source */
        $source = Repo::getRecords('sources' , ['*'], ['name'=>$sourceName])->first();
        if(isset($source)){
            $fetchUrl = $source->fetch_url;
            if(isset($fetchUrl)){
                $this->insertFetchingLog($source);

                [$donePages, $failedPages] = $this->fetch($fetchUrl, $source);

                $this->printInfo(['total fetched pages: '.$donePages,'total failed pages: '.$failedPages]);
            }else{
                $this->printInfo(['Fetch Url not found']);
            }
        }else{
            $this->printInfo(['Source not found']);
        }
    }

    /**
     * @param stdClass $source
     */
    private function insertFetchingLog(stdClass $source): void
    {
        $fetchStartCat = Repo::getRecords('logcategories', ['*'], ['name' => 'fetching'])->first();
        Repo::insertRecord('logs', [
            'source_id' => optional($source)->id,
            'category_id' => optional($fetchStartCat)->id,
            'text' => 'Fetching started',
            'created_at' => Carbon::now(),
        ]);
    }

    /**
     * @param string $fetchUrl
     * @param stdClass $source
     * @param AdFetcher $adFetcher
     * @param AdItemInserter $adItemInserter
     * @param AdPicTransferrer $adPicTransferrer
     * @return array
     */
    private function fetch(string $fetchUrl, stdClass $source): array
    {
        if ($this->confirm('Do you want to fetch all of items?', true)) {
            $page = 1;
        }else{
            $page = $this->adFetcher->getPageToFetch($source);
        }
        $this->printInfo(['Start fetching from page '.$page]);

        $failedPages = 0;
        do {
            $counter = 0;
            [$fetchDone , $items , $perPage , $nextPage , $resultText] = $this->adFetcher->fetchAd($fetchUrl, $page);
            if ($fetchDone) {
                if (empty($items)) {
                    $this->printInfo(["No $items fetched in request for page $page"]);
                    continue;
                }

                $this->printInfo(['Inserting '.count($items).' items']);
                $bar = $this->output->createProgressBar(count($items));
                foreach ($items as $key => $item) {
                    if($this->adItemInserter->storeItem($source, $item , $this->adPicTransferrer)){
                        $bar->advance();
                        $counter++;
                    }
                }
                $bar->finish();
                $bar->setProgress($counter);
                $this->info("\n");

                $firstItemId = optional($items[0])->id;
            } else {
                $this->printInfo(["Failure on fetching page $page","response: $resultText"]);
                $failedPages++;
            }

            $this->insertFetchLog($source, (isset($firstItemId)) ? $firstItemId : null, $page, $perPage, $counter);

            $page = $nextPage;
        } while (isset($page));

        return [$page, $failedPages];
    }

    /**
     * @param stdClass $source
     * @param $firstItem
     * @param int $page
     * @param int $perPage
     * @param int $done
     */
    private function insertFetchLog(stdClass $source, $firstItem, int $page, int $perPage, int $done): void
    {
        Repo::insertRecord('fetches', [
            'source_id' => $source->id,
            'first_item_id' => $firstItem,
            'page' => $page,
            'per_page' => $perPage,
            'fetched' => $done,
            'created_at' => Carbon::now(),
        ]);
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
}
