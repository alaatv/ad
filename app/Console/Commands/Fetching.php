<?php

namespace App\Console\Commands;

use App\Jobs\FetchAd;
use Illuminate\Console\Command;

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


    /**
     * Execute the console command.
     *
     */
    public function handle():void
    {
        $sourceName = $this->argument('source');
        dispatch(new FetchAd($sourceName));
    }
}
