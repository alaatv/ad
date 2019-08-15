<?php


namespace App\Classes;


use App\Adapter\AlaaSftpAdapter;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Storage;
use stdClass;

class AdPicGenerator
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

    public function generatePicObject() {
        $this->ad->image = $this->imageBlockFormatter() ;
    }

    private function imageBlockFormatter() : array {
        $adPicDimension = $this->getAdPicDimension();
        return [
            'image'   => $this->getAdPicLink(),
            'width'   => $adPicDimension['width'],
            'height'  => $adPicDimension['height'],
        ];
    }

    /**
     * @return array|null
     */
    private function getAdPicDimension() :array {
        return (new AdPicDimensionFinder($this->ad))->findDimension();
    }

    /**
     * @return string
     */
    private function getAdPicLink(): string
    {
        return (new AdPicLinkGenerator($this->ad))->generatePicLink();
    }
}
