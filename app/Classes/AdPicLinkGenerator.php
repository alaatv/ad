<?php


namespace App\Classes;


use App\Adapter\AlaaSftpAdapter;
use Illuminate\Support\Facades\Storage;
use stdClass;

class AdPicLinkGenerator
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

    public function generatePicLink()
    {
        /** @var AlaaSftpAdapter $diskAdapter */
        $diskAdapter = Storage::disk('alaaCdnSFTP')->getAdapter();
        $this->ad->image = $diskAdapter->getUrl(optional($this->ad)->image);
    }
}
