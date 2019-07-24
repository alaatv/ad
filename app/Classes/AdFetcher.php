<?php


namespace App\Classes;


use App\Repositories\Repo;
use App\Traits\HTTPRequestTrait;
use Illuminate\Http\Response;
use stdClass;

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
            $resultText = 'Fetched successfully';
        }else{
            $done = false;
            $resultText = isset($result->error->message)?$result->error->message:'No response message received';
        }
        return [
            $done ,
            (isset($data))?$data:null ,
            (isset($perPage))?$perPage:null ,
            (isset($nextPage))?$nextPage:null ,
            $resultText,
        ];
    }

    /**
     * @param stdClass $source
     * @return int
     */
    public function getPageToFetch(stdClass $source):int
    {
        $lastFetch = Repo::getRecords('fetches', ['*'], ['source_id' => $source->id ])->where('fetched' , '>' , 0)->orderByDesc('page')->first();
        $page = $lastFetch->page;
        if ($lastFetch->per_page == $lastFetch->fetched) {
            $page = $lastFetch->page + 1;
        }
        return $page;
    }


}
