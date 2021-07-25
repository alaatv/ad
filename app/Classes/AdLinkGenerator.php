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

    public function generateLink()
    {
        $this->ad->link = $this->concatRedirectQueryToUrl($this->getAdBasicUrl());
    }

    /**
     * @return string
     */
    private function getAdBasicUrl(): string
    {
        return route('ad.click', ['UUID' => $this->ad->UUID ]).'?';
    }

    private function concatRedirectQueryToUrl(string $baseUrl):string
    {
        return $baseUrl.'&redirect='.$this->ad->link;
    }
}
