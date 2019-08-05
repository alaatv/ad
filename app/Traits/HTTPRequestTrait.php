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
        try {
            $res = $client->request($method, $path, [
                'query'  => $parameters,
                'header' => $headers,
                'sink'   => $sink
            ]);
        } catch (GuzzleException $e) {
            Log::error($e->getMessage());
            throw new Exception($e->getMessage());
        }

        return [
            'statusCode' => $res->getStatusCode(),
            'result'     => $res->getBody()->getContents(),
        ];
    }

    protected function setErrorResponse(int $responseCode, string $responseText): array
    {
        return [
            'error' => [
                'code' => $responseCode,
                'message' => $responseText,
            ]
        ];
    }
}
