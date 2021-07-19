<?php

namespace App\Classes;

class AdRedirectUrlGenerator
{
    private $ad;

    /**
     * AdRedirectUrlGenerator constructor.
     * @param $ad
     */
    public function __construct($ad)
    {
        $this->ad = $ad;
    }


    /**
     * @param string $basicUrl
     * @return string
     */
    public function generateUrl(): string
    {
        return $this->concatUtmParameters(removeParametersFromUrl($this->ad->link));
    }

    /**
     * @param string $url
     * @return string
     */
    private function concatUtmParameters(string $url): string
    {
        foreach ((new AdUTMGenerator($this->ad))->generateUTMArray() as $utmParameter => $value) {
            $url = concatParameterToUrl($url, $utmParameter, $value);
        }
        return $url;
    }
}
