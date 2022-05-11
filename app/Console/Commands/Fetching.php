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
    protected $signature = 'ad:fetch {source : name of the source to be fetched} {--since=} {--till=}';

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
    public function handle(): int
    {
        $sourceName = $this->argument('source');
        $since = $this->option('since');
        $till = $this->option('till');
        if ($this->confirm('Did you define since and till dates?')) {
            dispatch(new FetchAd($sourceName, $since, $till));
            return 0;
        }
        return 0;
    }
}
