<?php


namespace App\Traits;


use GuzzleHttp\{Client, Exception\GuzzleException};
use Illuminate\{Http\Request, Support\Facades\Log};
use PHPUnit\Framework\Exception;

trait HTTPRequestTrait
{
    protected function sendRequest(string $path, string $method, array $parameters = [] , array $headers = [])
    {
        $client  = new Client();
        $request = new Request();
        foreach ($parameters as $key => $parameter) {
            $request->offsetSet($key, $parameter);
        }
        try {
            $res = $client->request($method, $path, [
                'form_params' => $request->all() ,
                'headers' => $headers
            ]);
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
        $response = [
            'error' => [
                'code' => $responseCode,
                'message' => $responseText,
                'extraInfo' => $extraInfo,
            ]
        ];
        return $response;
    }

    protected function setSuccessResponse(string $responseText , array $data= []): array
    {
        $response = [
            'message'   =>  $responseText,
            'data'      => $data
        ];
        return $response;
    }
}
