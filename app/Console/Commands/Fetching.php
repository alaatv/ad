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
    protected $signature = 'ad:fetch {source : name of the source to be fetched}';

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
    public function handle(): int
    {
        $sourceName = $this->argument('source');
        dispatch(new FetchAd($sourceName));
        return 0;
    }
}
