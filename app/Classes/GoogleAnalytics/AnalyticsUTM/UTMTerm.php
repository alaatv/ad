<?php


namespace App\Classes\GoogleAnalytics\AnalyticsUTM;


class UTMTerm extends AnalyticsUTMGenerator
{
    public function generateUTM()
    {
        return $this->ad->foreign_id;
    }
}
