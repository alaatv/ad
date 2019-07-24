<?php


namespace App\Classes;


use App\Traits\HTTPRequestTrait;
use Illuminate\Http\Response;

class AdFetcher
{
    use HTTPRequestTrait;

    /**
     * @param string $fetchUrl
     * @param int $page
     * @return array
     */
    public function fetchAd(string $fetchUrl, int $page): array
    {
        $headers = [
            'Content-Type' => 'application/json',
            'Accept' => 'application/json',
            'X-Requested-With' => 'XMLHttpRequest'
        ];
        $parameters = ['page' => $page];
        $response = $this->sendRequest($fetchUrl, 'POST', $parameters, $headers);
        $result = json_decode($response['result']);
        if($response['statusCode'] == Response::HTTP_OK){
            $done = true;
            $data = (isset($result->data)) ? $result->data : [];
            $perPage  = optional($result)->per_page;
            $nextPage = optional($result)->next_page;
            $message = 'Fetched successfully';
        }else{
            $done = false;
            $message = isset($result->error->message)?$result->error->message:'No response message received';
        }
        return [
            $done ,
            (isset($data))?$data:null ,
            (isset($perPage))?$perPage:null ,
            (isset($nextPage))?$nextPage:null ,
            $message,
        ];
    }
}
