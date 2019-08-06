<?php


namespace App\Classes;


use App\Classes\GoogleAnalytics\AnalyticsUTM\UTMCampaign;
use App\Classes\GoogleAnalytics\AnalyticsUTM\UTMContent;
use App\Classes\GoogleAnalytics\AnalyticsUTM\UTMMedium;
use App\Classes\GoogleAnalytics\AnalyticsUTM\UTMSource;
use App\Classes\GoogleAnalytics\AnalyticsUTM\UTMTerm;

class AdUTMGenerator
{
    private $ad;

    /**
     * AdUTMGenerator constructor.
     * @param $ad
     */
    public function __construct($ad)
    {
        $this->ad = $ad;
    }

    /**
     * @return array
     */
    public function generateUTMArray(): array
    {
        return [
            'utm_term'      => (new UTMTerm($this->ad))->generateUTM(),
            'utm_source'    => (new UTMSource($this->ad))->generateUTM(),
            'utm_content'   => (new UTMContent($this->ad))->generateUTM(),
            'utm_medium'    => (new UTMMedium($this->ad))->generateUTM(),
            'utm_campaign'  => (new UTMCampaign($this->ad))->generateUTM()
        ];
    }
}
