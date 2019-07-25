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
            $fetchUrl = $source->fetch_url.'?timestamp=2016-03-01';
        } else {
            if ($lastFetch->current_page < $lastFetch->last_page) {
                $fetchUrl = $lastFetch->next_page_url;
            } else {
                $fetchUrl = $source->fetch_url . '?timestamp=' . $lastFetch->updated_at;
            }
        }
        return $fetchUrl;
    }
}
