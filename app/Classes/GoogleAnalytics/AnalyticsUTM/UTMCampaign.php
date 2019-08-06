<?php


namespace App\Classes\GoogleAnalytics\AnalyticsUTM;


use App\Repositories\AdRepo;

class UTMCampaign extends AnalyticsUTMGenerator
{
    public function generateUTM()
    {
        return AdRepo::UTM_CAMPAIGN;
    }
}
