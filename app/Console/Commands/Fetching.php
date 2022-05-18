<?php

namespace App\Console\Commands;

use App\Classes\AdFetcher;
use App\Classes\AdItemInserter;
use App\Classes\AdPicTransferrer;
use App\Jobs\FetchAd;
use Illuminate\Console\Command;

class Fetching extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'ad:fetch {source : name of the source to be fetched} {--since=}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Fetching ads from a source';


    /**
     * Execute the console command.
     *
     */
    public function handle(AdFetcher $adFetcher, AdItemInserter $adItemInserter, AdPicTransferrer $adPicTransferrer): int
    {
        $sourceName = $this->argument('source');
        $since = $this->option('since');
        dispatch(new FetchAd($sourceName, $since, $adFetcher, $adItemInserter, $adPicTransferrer));
        return 0;
    }
}
