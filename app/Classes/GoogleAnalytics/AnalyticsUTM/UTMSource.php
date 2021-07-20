<?php


namespace App\Classes\GoogleAnalytics\AnalyticsUTM;

use App\Repositories\AdRepo;

class UTMSource extends AnalyticsUTMGenerator
{
    public function generateUTM()
    {
        return AdRepo::UTM_SOURCE;
    }
}
