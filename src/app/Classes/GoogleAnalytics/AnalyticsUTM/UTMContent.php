<?php


namespace App\Classes\GoogleAnalytics\AnalyticsUTM;

use App\Repositories\Repo;

class UTMContent extends AnalyticsUTMGenerator
{
    public function generateUTM()
    {
        return $this->ad->referer;
    }
}
