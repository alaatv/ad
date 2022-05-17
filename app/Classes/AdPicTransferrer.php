<?php


namespace App\Classes;


use App\Traits\HTTPRequestTrait;
use Exception;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\Response;

class AdPicTransferrer
{
    use HTTPRequestTrait;

    /**
     * @param string $picUrl
     * @return array
     */
    public function storeAdPic(string $picUrl = null): array
    {
        try {
            if (!isset($picUrl))
                return [false, null];

            $pathToSave = Storage::disk('adImage')->putFileAs('', $picUrl, basename($picUrl));
            $filePath = fopen(storage_path('app/public/images/ads/') . $pathToSave, 'w+');

            $response = $this->sendRequest($picUrl, 'GET', null, null, $filePath);
            if ($response['statusCode'] == Response::HTTP_OK) {
                return [true, 'app/public/images/ads/' . $pathToSave];
            }
            return [false, null];

        } catch (Exception $e) {
            return [false, null];
        }
    }

    /**
     * @param string $filePath
     * @return
     */
    public function transferAdPicToCDN(string $filePath): array
    {
        $disk = Storage::disk('adPicsSFTP');
        $fileName = basename($filePath);
        $url = null;
        $done = false;

        //TODO : login to filesystem in production ENV (set correct username and host)
        if ($disk->put($fileName, File::get(storage_path($filePath)))) {
            $url = config('download_server.IMAGES_PARTIAL_PATH') . '/' . $fileName;
            $done = true;
            Storage::disk('adImage')->delete($fileName);
        }
        return [$done, $url];
    }
}
