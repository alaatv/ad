<?php


namespace App\Classes;


use stdClass;

class AdLinkGenerator
{
    protected  $ad;

    /**
     * adLinkGenerator constructor.
     * @param stdClass $ad
     */
    public function __construct(stdClass $ad)
    {
        $this->ad = $ad;
    }

    public function generateLink(){
        $this->ad->link = env('APP_URL').'/ad/'.$this->ad->UUID.'/click';
    }

    /**
     * @param stdClass $ad
     * @return AdLinkGenerator
     */
    public function setAd(stdClass $ad): AdLinkGenerator
    {
        $this->ad = $ad;
        return $this;
    }
}
