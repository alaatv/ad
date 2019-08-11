<?php
namespace App\Classes\GoogleAnalytics\AnalyticsMeasurement;

use Illuminate\Support\Arr;

class EventTrackingHit extends AnalyticsMeasurementRecorder
{
    const HIT_TYPE  = 'event';

    /**
     * @param array $bundle
     * @return AnalyticsMeasurementRecorder
     */
    public function addParameters(array $bundle):AnalyticsMeasurementRecorder
    {
        $this->parameters[] = [
            't'   => self::HIT_TYPE,
            'ec'  => Arr::get($bundle , 'ec') ,
            'ea'  => Arr::get($bundle , 'ea') ,
            'el'  => Arr::get($bundle , 'el') ,
            'ev'  => Arr::get($bundle , 'ev'),
        ];

        return $this;
    }
}
