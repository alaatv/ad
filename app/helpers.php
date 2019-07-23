<?php
if (!function_exists('convertTagStringToArray')) {
    function convertTagStringToArray($string): array
    {
        $tags = explode(",", $string);
        $tags = array_filter($tags);

        return $tags;
    }

}
