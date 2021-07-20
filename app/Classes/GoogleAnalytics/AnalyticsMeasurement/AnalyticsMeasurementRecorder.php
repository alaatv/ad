<?php
namespace App\Classes\GoogleAnalytics\AnalyticsMeasurement;

use App\Traits\HTTPRequestTrait;
use Illuminate\Http\Response;

abstract class AnalyticsMeasurementRecorder
{
    use HTTPRequestTrait;

    const HOST    = 'www.google-analytics.com';
    const PATH    = '/collect';
    const METHOD  = 'POST';
    const VERSION = 1;

    protected $parameters;

    /**
     * GoogleAnalyticsGenerator constructor.
     * @param string $trackingID
     * @param int $clientID
     */
    public function __construct(string $trackingID , int $clientID)
    {
        $this->parameters = [
            'v'   => self::VERSION,
            'tid' => $trackingID,
            'cid' => $clientID,
        ];
    }

    /**
     * @param array $bundle
     * @return AnalyticsMeasurementRecorder
     */
    public abstract function addParameters(array $bundle):self;

    /**
     * @return array
     */
    public function send():array{
        $response = $this->sendRequest(self::HOST . self::PATH, self::METHOD , $this->parameters);
        if($response['statusCode'] == Response::HTTP_OK){
            return [true , null ];
        }

        return [false , $response['result']];
    }
}
