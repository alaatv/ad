<?php


namespace App\Classes;


use App\Adapter\AlaaSftpAdapter;
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

    public function generatePicLink() :string {
        /** @var AlaaSftpAdapter $diskAdapter */
        $diskAdapter = Storage::disk('alaaCdnSFTP')->getAdapter();
        return $diskAdapter->getUrl(optional($this->ad)->image);
    }
}
