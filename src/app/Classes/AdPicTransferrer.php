<?php


namespace App\Classes;


use App\Traits\HTTPRequestTrait;
use Exception;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
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
        try{
            Log::info('pic url:'. $picUrl);
            if(!isset($picUrl))
                return [false, null];

            $basePath = explode('app/', __DIR__)[0];
            //ToDo: Hard Code
            $pathToSave = $basePath . 'storage/app/public/images/ads/' . basename($picUrl);
            Log::info('path to save:'.$pathToSave);
            $filePath = fopen($pathToSave, 'w');

            $response = $this->sendRequest($picUrl, 'GET', null, null , $filePath);
            Log::info('store pic response:'.$response['statusCode']);
            if($response['statusCode'] == Response::HTTP_OK){
                return [true,$pathToSave];
            }
            return [false, null];

        } catch ( Exception $e ) {
            return [false, null];
        }
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
        Log::info('transfer to cnd file path:'.$filePath);
        if ($disk->put($fileName, File::get($filePath))) {
            $url = config('download_server.IMAGES_PARTIAL_PATH'). '/'.$fileName;
            $done = true;
            //ToDo : Uncomment
//            Storage::disk('adImage')->delete($fileName);
        }
        Log::info('transfer to cnd done:'.$done);
        return [$done, $url];
    }
}
