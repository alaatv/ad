<?php


namespace App\Classes\GoogleAnalytics\AnalyticsUTM;

class UTMContent extends AnalyticsUTMGenerator
{
    public function generateUTM()
    {
        return $this->ad->referer;
    }
}
