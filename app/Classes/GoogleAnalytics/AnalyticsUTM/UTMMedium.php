<?php


namespace App\Classes\GoogleAnalytics\AnalyticsUTM;


use App\Repositories\AdRepo;

class UTMMedium extends AnalyticsUTMGenerator
{
    public function generateUTM()
    {
        return AdRepo::UTM_MEDIUM;
    }
}
