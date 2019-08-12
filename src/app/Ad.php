<?php

namespace App;

use stdClass;

class Ad
{
    public static function setReferer(stdClass $ad , string $referer=null)
    {
        $ad->referer = $referer;
    }
}
