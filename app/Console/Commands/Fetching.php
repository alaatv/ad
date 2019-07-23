<?php

namespace App\Console\Commands;

use App\Repositories\Repo;
use App\Traits\HTTPRequestTrait;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Http\Response;
use Illuminate\Support\Str;
use stdClass;

class Fetching extends Command
{
    use HTTPRequestTrait;

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

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
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

                [$donePages, $failedPages] = $this->doFetching($fetchUrl, $source);

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
     * @param $fetchUrl
     * @param $source
     * @return array
     */
    private function doFetching(string $fetchUrl,stdClass $source): array
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
            [$fetchDone , $items , $perPage , $nextPage , $message] = $this->fetchAd($fetchUrl, $page);
            if ($fetchDone) {
                if (empty($items)) {
                    $this->info("No $items fetched on request for page $page");
                    $this->info("\n");
                    continue;
                }

                $this->info('Inserting '.count($items).' items');
                $bar = $this->output->createProgressBar(count($items));
                foreach ($items as $key => $item) {
                    if($this->isValidItem($item) && $this->isInsertable($this->makeAdForeignId($source->id, optional($item)->id))){
                        [$storeResult,$picPath] =  $this->storeAdPic(optional($item)->image);
                        if($storeResult){
                            $item->image = $picPath;
                        }

                        $this->insertAdRecord($source, $item);
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
     * @param $item
     */
    private function insertAdRecord(stdClass $source, $item): void
    {
        Repo::insertRecord('ads', [
            'UUID'  => Str::uuid()->toString() ,
            'source_id' => $source->id,
            'foreign_id' => $this->makeAdForeignId($source->id , optional($item)->id),
            'name' => optional($item)->name,
            'image' => optional($item)->image,
            'link' => optional($item)->link,
            'enable' => 1,
            'created_at' => Carbon::now(),
        ]);
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
     * @param string $fetchUrl
     * @param int $page
     * @return array
     */
    private function fetchAd(string $fetchUrl, int $page): array
    {
        $headers = [
            'Content-Type' => 'application/json',
            'Accept' => 'application/json',
            'X-Requested-With' => 'XMLHttpRequest'
        ];
        $parameters = ['page' => $page];
        $response = $this->sendRequest($fetchUrl, 'POST', $parameters, $headers);
        $result = json_decode($response['result']);
        if($response['statusCode'] == Response::HTTP_OK){
            $done = true;
            $data = (isset($result->data)) ? $result->data : [];
            $perPage  = optional($result)->per_page;
            $nextPage = optional($result)->current_page + 1;
            $message = 'Fetched successfully';
        }else{
            $done = false;
            $message = isset($result->error->message)?$result->error->message:'No response message received';
        }
        return [
            $done ,
            (isset($data))?$data:null ,
            (isset($perPage))?$perPage:null ,
            (isset($nextPage))?$nextPage:null ,
            $message,
        ];
    }

    /**
     * @param $item
     * @return bool
     */
    private function isValidItem($item):bool
    {
        return isset($item->id) && isset($item->name) && isset($item->link) && isset($item->image);
    }

    private function makeAdForeignId(int $sourceId, int $itemId):string
    {
        return 'source'.$sourceId.'_'.$itemId;
    }


    private function isInsertable(string $adId):bool
    {
        $ad = Repo::getRecords('ads', ['id'] ,['foreign_id'=>$adId])->first();
        return (isset($ad))?true:false;
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
     * @param string $picUrl
     * @return array
     */
    private function storeAdPic(string $picUrl=null): array
    {
        if(!isset($picUrl))
            return [false, null];

        $basePath = explode('app/', __DIR__)[0];
        $pathToSave = $basePath . 'storage/app/public/images/ads/' . basename($picUrl);
        $filePath = fopen($pathToSave, 'w');

        $response = $this->sendRequest($picUrl, 'GET', [], [] , $filePath);
        if($response['statusCode'] == Response::HTTP_OK){
            return [true,$pathToSave];
        }
        return [false, null];

    }
}
