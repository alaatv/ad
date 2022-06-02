<?php

namespace App\Classes;

use App\interfaces\AdImageInterface;
use App\Traits\HTTPRequestTrait;
use Illuminate\Support\Facades\Storage;

class AdImageUtil implements AdImageInterface
{

    use HTTPRequestTrait;

    public string $fileName;

    public function setFileName(string $filePath): static
    {
        $this->fileName = basename($filePath);
        return $this;
    }

    public function uploadImageToMinio($file): bool
    {
        return Storage::cloud()->put($this->fileName, $file);
    }
}
