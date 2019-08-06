<?php


namespace App\Classes\GoogleAnalytics\AnalyticsUTM;


use stdClass;

abstract class AnalyticsUTMGenerator
{
    protected $ad;

    /**
     * AnalyticsUTMGenerator constructor.
     * @param $ad
     */
    public function __construct(stdClass $ad)
    {
        $this->ad = $ad;
    }

    abstract public function generateUTM();
}
