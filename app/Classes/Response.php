<?php


namespace App\Classes;


class Response
{
    const FETCH_URL_NOT_FOUND = 1;
    const SOURCE_UNAVAILABLE = 2;
    const SOURCE_DISABLED = 3;
    const AD_NOT_FOUND = 4;
    const AD_UPDATE_DATABASE_ERROR = 5;
    const USER_NOT_FOUND = 6;
    const USER_NOT_HAVE_SOURCE = 7;
    const NO_VALID_SOURCE_FOUND_FOR_CUSTOMER = 8;
}
