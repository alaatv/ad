<?php

namespace App\Traits;

trait adTrait
{
    protected function makeAdForeignId(int $sourceId, int $itemId , string $itemType=null):string
    {
        $id = 's'.$sourceId;
        if(!is_null($itemType)){
            $id .= '_'.$itemType;
        }

        return $id.'_'.$itemId;
    }
}
