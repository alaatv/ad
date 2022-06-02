<?php

namespace App\interfaces;

interface AdImageInterface
{
    public function uploadImageToMinio($file): bool;

}
