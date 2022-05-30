<?php


namespace App\Classes;


use Illuminate\Support\Facades\Storage;
use stdClass;

class AdPicLinkGenerator
{
    private $ad;

    /**
     * AdPicLinkGenerator constructor.
     * @param stdClass $ad
     */
    public function __construct(stdClass $ad)
    {
        $this->ad = $ad;
    }

    public function generatePicLink(): string
    {
        return Storage::disk('adsMinio')->url('images/tabligh/' . $this->ad->image);
    }
}
