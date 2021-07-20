<?php


namespace App\Classes;

use Illuminate\Support\Arr;

class AdPicDimensionFinder
{
    private $ad;
    const  DIMENSION_MAP  = [
            'unknown' => [
                'width' => null ,
                'height'=> null,
            ],
            '1-product' => [
                'width' => 1 ,
                'height'=> 1,
            ],
            '1-content' => [
                'width' => 16 ,
                'height'=> 9,
            ],
            '2-product' => [
                'width' => 87 ,
                'height'=> 118,
            ],
        ];


    /**
     * AdPicDimensionFinder constructor.
     * @param $ad
     */
    public function __construct($ad){
        $this->ad = $ad;
    }

    public function findDimension():?array {
        if(!Arr::has(self::DIMENSION_MAP , $this->ad->source_id.'-'.$this->ad->type ))
            return self::DIMENSION_MAP['unknown'];

        return self::DIMENSION_MAP[$this->ad->source_id.'-'.$this->ad->type];
    }

}
