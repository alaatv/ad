<?php

namespace App\Traits;

trait adTrait
{
    protected function makeAdForeignId(int $sourceId, int $itemId):string
    {
        return 's'.$sourceId.'_'.$itemId;
    }
}
