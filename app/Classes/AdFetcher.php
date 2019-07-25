<?php


namespace App\Classes;


use App\Repositories\Repo;
use App\Traits\HTTPRequestTrait;
use Illuminate\Http\Response;
use stdClass;

class AdFetcher
{
    use HTTPRequestTrait;

    const FIRST_FETCH_DATE = '2016-03-01';

    /**
     * @param string $fetchUrl
     * @return array
     */
    public function fetchAd(string $fetchUrl): array
    {
        $headers = [
            'Content-Type' => 'application/json',
            'Accept' => 'application/json',
            'X-Requested-With' => 'XMLHttpRequest'
        ];
        $response = $this->sendRequest($fetchUrl, 'POST', [], $headers);
        $result = json_decode($response['result']);
        if($response['statusCode'] == Response::HTTP_OK){
            $done = true;
            $data = (isset($result->data)) ? $result->data : [];
            $currentPage  = optional($result)->current_page;
            $nextPageUrl = optional($result)->next_page_url;
            $lastPage = optional($result)->last_page;
            $resultText = 'Fetched successfully';
        }else{
            $done = false;
            $resultText = isset($result->error->message)?$result->error->message:'No response message received';
        }

        return [
            $done ,
            (isset($data))?$data:null ,
            (isset($currentPage))?$currentPage:null ,
            (isset($nextPageUrl))?$nextPageUrl:null ,
            (isset($lastPage))?$lastPage:null ,
            $resultText,
        ];
    }

    /**
     * @param stdClass $source
     * @return string
     */
    public function getFetchUrl(stdClass $source):string
    {
        $lastFetch = Repo::getRecords('fetches', ['*'], ['source_id' => $source->id])->orderByDesc('created_at')->first();
        if (is_null($lastFetch)) {
            return $source->fetch_url.'?timestamp='.self::FIRST_FETCH_DATE;
        }

        if ($lastFetch->current_page < $lastFetch->last_page) {
            return  $lastFetch->next_page_url;
        } else {
            return $source->fetch_url . '?timestamp=' . $lastFetch->updated_at;
        }
    }
}
