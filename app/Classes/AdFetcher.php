<?php


namespace App\Classes;


use App\Traits\HTTPRequestTrait;
use Symfony\Component\HttpFoundation\Response;

class AdFetcher
{
    use HTTPRequestTrait;

    /**
     * @param string $fetchUrl
     * @return array
     */
    public function fetchAd(string $fetchUrl): array
    {
        return $this->getRequestResult($this->sendRequest($fetchUrl, 'POST'));
    }

    /**
     * @param array $response
     * @return array
     */
    private function getRequestResult(array $response): array
    {
        $result = json_decode($response['result']);
        if ($response['statusCode'] == Response::HTTP_OK) {
            $done = true;
            $data = (isset($result->data)) ? $result->data : [];
            $currentPage = optional($result)->current_page;
            $nextPageUrl = optional($result)->next_page_url;
            $lastPage = optional($result)->last_page;
            $resultText = 'Fetched successfully';
        } else {
            $done = false;
            $resultText = $result->error->message ?? 'No response message received';
        }

        return [
            $done,
            (isset($data)) ? $data : null,
            (isset($currentPage)) ? $currentPage : null,
            (isset($nextPageUrl) && strlen($nextPageUrl) > 0) ? $nextPageUrl : null,
            (isset($lastPage)) ? $lastPage : null,
            $resultText,
        ];
    }
}
