<?php


namespace App\Classes;

use Exception;
use Illuminate\Support\Facades\Log;
use JetBrains\PhpStorm\Pure;

class AdPicTransferrer
{
    protected AdImageUtil $adImageUtil;

    #[Pure] public function __construct()
    {
        $this->adImageUtil = new AdImageUtil;
    }

    /**
     * @param string|null $picUrl
     * @return array
     */
    public function storeAdPic(string $picUrl = null): array
    {
        try {
            if (!isset($picUrl))
                return [false, null];

            $isPicLoaded = $this->adImageUtil->setFileName($picUrl)->uploadImageToLocal($picUrl)
                ->getImageLocalFullPath()->testImageLoading($picUrl);
            if ($isPicLoaded) {
                return [true, 'app/public/images/ads/' . $this->adImageUtil->pathToSave];
            }
            return [false, null];
        } catch (Exception $exception) {
            Log::error('image is not stored in local | ' . $exception->getMessage());
            return [false, null];
        }
    }

    /**
     * @param string $filePath
     * @return array
     */
    public function transferAdPicToMinio(string $filePath): array
    {
        $url = null;
        $done = false;
        if ($this->adImageUtil->setFileName($filePath)->uploadImageToMinio($filePath)) {
            $url = $this->adImageUtil->fileName;
            $done = true;
            $this->adImageUtil->deleteImageFromLocal($this->adImageUtil->fileName);
        }
        return [$done, $url];
    }
}
