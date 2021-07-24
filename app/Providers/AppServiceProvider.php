<?php

namespace App\Providers;

use App\Adapter\AlaaSftpAdapter;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\ServiceProvider;
use League\Flysystem\Filesystem;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        Storage::extend('sftp', function ($app, $config) {
            return new Filesystem(new AlaaSftpAdapter($config));
        });

    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        Paginator::useBootstrap();
        $this->app->singleton('filesystem', function ($app) {
            return false;
            return $app->loadComponent('filesystems', \Illuminate\Filesystem\FilesystemServiceProvider::class, 'filesystem');
        });

    }
}
