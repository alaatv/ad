<?php


namespace App\Traits;


use GuzzleHttp\{Client, Exception\GuzzleException};
use Illuminate\{Support\Facades\Log};
use PHPUnit\Framework\Exception;

trait HTTPRequestTrait
{
    protected function sendRequest(string $path, string $method, array $parameters = [] , array $headers = [] ,  $sink=null)
    {
        $client  = new Client();

        $options = [];

        if(!empty($parameters)){
            $options['query'] = $parameters;
        }

        if(!empty($headers)){
            $options['headers'] = $headers;
        }

        if(isset($sink)){
            $options['sink'] = $sink;
        }

        try {
            $res = $client->request($method, $path, $options);
        } catch (GuzzleException $e) {
            Log::error($e->getMessage());
            throw new Exception($e->getMessage());
        }

        return [
            "statusCode" => $res->getStatusCode(),
            "result"     => $res->getBody()->getContents(),
        ];
    }

    protected function setErrorResponse(int $responseCode, string $responseText , array $extraInfo = []): array
    {
        return [
            'error' => [
                'code' => $responseCode,
                'message' => $responseText,
                'extraInfo' => $extraInfo,
            ]
        ];
    }

    protected function setSuccessResponse(string $responseText , array $data= []): array
    {
        return [
            'message'   =>  $responseText,
            'data'      => $data
        ];
    }
}
