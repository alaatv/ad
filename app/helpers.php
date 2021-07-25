<?php
if (!function_exists('convertTagStringToArray')) {
    function convertTagStringToArray($string): array
    {
        $tags = explode(",", $string);
        $tags = array_filter($tags);

        return $tags;
    }
}

if (!function_exists('removeParametersFromUrl')) {
    function removeParametersFromUrl(string $basicUrl): string
    {
        return explode('?', $basicUrl)[0] . '?';
    }
}
if (!function_exists('concatParameterToUrl')) {
    function concatParameterToUrl(string $basicUrl, $name, $value): string
    {
        return $basicUrl.'&' . $name . '=' . $value;
    }
}
