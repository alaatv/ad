<?php

namespace App\Console\Commands;

use App\Classes\AdFetcher;
use App\Classes\AdItemInserter;
use App\Classes\AdPicTransferrer;
use App\Repositories\Repo;
use App\Traits\adTrait;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Str;
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

                $this->info('total fetched pages: '.$donePages);
                $this->info("\n");
                $this->info('total failed pages: '.$failedPages);
            }else{
                $this->info('Fetch Url not found');
                $this->info("\n");
            }
        }else{
            $this->info('Source not found');
            $this->info("\n");
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
            $page = $this->getPageToFetch($source);
        }
        $this->info('Start fetching from page '.$page);

        $failedPages = 0;
        do {
            $counter = 0;
            [$fetchDone , $items , $perPage , $nextPage , $message] = $this->adFetcher->fetchAd($fetchUrl, $page);
            if ($fetchDone) {
                if (empty($items)) {
                    $this->info("No $items fetched in request for page $page");
                    $this->info("\n");
                    continue;
                }

                $this->info('Inserting '.count($items).' items');
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
                $this->info("Failure on fetching page $page");
                $this->info("\n");
                $this->info("response: $message");
                $this->info("\n");
                $failedPages++;
            }

            $this->insertFetchLog($source, (isset($firstItemId)) ? $firstItemId : null, $page, $perPage, $counter);

            $page = $nextPage;
        } while (isset($page));

        return [$page, $failedPages];
    }

    /**
     * @param stdClass $source
     * @return int
     */
    private function getPageToFetch(stdClass $source):int
    {
        $lastFetch = Repo::getRecords('fetches', ['*'], ['source_id' => $source->id ])->where('fetched' , '>' , 0)->orderByDesc('page')->first();
        $page = $lastFetch->page;
        if ($lastFetch->per_page == $lastFetch->fetched) {
            $page = $lastFetch->page + 1;
        }
        return $page;
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
}
