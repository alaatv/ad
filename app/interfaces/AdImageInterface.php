<?php

namespace App\interfaces;

interface AdImageInterface
{
    public function uploadImageToMinio($filePath): bool;

    public function uploadImageToLocal(string $picUrl): static;

}
