<?php

namespace App\Classes;

use App\interfaces\AdImageInterface;
use App\Traits\HTTPRequestTrait;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\Response;

class AdImageUtil implements AdImageInterface
{

    use HTTPRequestTrait;

    public string $fileName;
    public string $pathToSave;
    public string $picPath;

    public function setFileName($filePath): static
    {
        $this->fileName = basename($filePath);
        return $this;
    }

    public function uploadImageToLocal(string $picUrl): static
    {
        $this->pathToSave = Storage::putFileAs('', $picUrl, $this->fileName);
        return $this;
    }

    public function uploadImageToMinio($filePath): bool
    {
        return Storage::cloud()->put($this->fileName, File::get(storage_path($filePath)));
    }

    public function deleteImageFromLocal($fileName)
    {
        Storage::delete($fileName);
    }

    public function getImageLocalFullPath(): static
    {
        $this->picPath = Storage::path($this->pathToSave);
        return $this;
    }

    public function testImageLoading(string $picUrl): bool
    {
        $response = $this->sendRequest($picUrl, 'GET', null, null, $this->picPath);
        if ($response['statusCode'] == Response::HTTP_OK) {
            return true;
        }
        return false;
    }


}
