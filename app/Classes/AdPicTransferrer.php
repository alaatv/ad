<?php


namespace App\Classes;


use App\Traits\HTTPRequestTrait;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;

class AdPicTransferrer
{
    use HTTPRequestTrait;

    /**
     * @param string $picUrl
     * @return array
     */
    public function storeAdPic(string $picUrl=null): array
    {
        if(!isset($picUrl))
            return [false, null];

        $basePath = explode('app/', __DIR__)[0];
        //ToDo: Hard Code
        $pathToSave = $basePath . 'storage/app/public/images/ads/' . basename($picUrl);
        $filePath = fopen($pathToSave, 'w');

        $response = $this->sendRequest($picUrl, 'GET', [], [] , $filePath);
        if($response['statusCode'] == Response::HTTP_OK){
            return [true,$pathToSave];
        }
        return [false, null];
    }

    /**
     * @param string $filePath
     * @return
     */
    public function transferAdPicToCDN(string $filePath):array {
        $disk = Storage::disk('adPicsSFTP');
        $fileName = basename($filePath);
        $url = null;
        $done = false;
        if ($disk->put($fileName, File::get($filePath))) {
            $url = config('download_server.SERVER_PROTOCOL').
                config('download_server.SERVER_NAME').
                config('download_server.IMAGES_PARTIAL_PATH').
                '/ads/'.$fileName;
            $done = true;
        }
        return [$done, $url];
    }
}