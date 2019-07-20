<?php

namespace App\Http\Controllers;

use App\Repositories\Repo;
use App\Traits\HTTPRequestTrait;
use Carbon\Carbon;
use Illuminate\{Http\JsonResponse, Http\Request, Http\Response};
use \App\Classes\Response as myResponse ;
use stdClass;

class HomeController extends Controller
{
    use HTTPRequestTrait;

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function index(Request $request){
        $adNumber = $request->get('number' , 6);
        $sourceName = $request->get('source');

        $source = Repo::getRecords('sources' , ['*'], ['name'=>$sourceName])->first();
        if(!isset($source)){
            return response()->json($this->setErrorResponse(myResponse::SOURCE_NOT_FOUND, 'Source not found'), Response::HTTP_NOT_FOUND);
        }

        if(!$source->enable){
            return response()->json($this->setErrorResponse(myResponse::SOURCE_DISABLED, 'Source is disable'), Response::HTTP_SERVICE_UNAVAILABLE);
        }

        $ads = Repo::getRecords('ads' , ['name' , 'link' , 'image'], ['source_id'=>$source->id , 'enable'=>1]);
        $ads = $ads->paginate($adNumber, ['*'], 'ads');
        return response()->json([
            [
                'title'  =>   $source->display_name,
                'color'  =>   'white',
                'icon'   =>   'icon',
                'data'  =>   $ads
             ]
        ]);
    }

    /**
     * Fetch ads from a source
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function fetchAds(Request $request){
        $sourceName = $request->get('source' , 'sourceName');

        /** @var stdClass $source */
        $source = Repo::getRecords('sources' , ['*'], ['name'=>$sourceName])->first();
        if(!isset($source)){
            return response()->json($this->setErrorResponse(myResponse::FETCH_URL_NOT_FOUND, 'Source not found'));
        }

        $fetchUrl = $source->fetch_url;
        if(!isset($fetchUrl)){
            return response()->json($this->setErrorResponse(myResponse::FETCH_URL_NOT_FOUND, 'Fetching url not found'));
        }

        $fetchStartLog =  Repo::getRecords('logcategories', ['*'] , ['name'=>'fetching'])->first();
        Repo::insertRecord('logs', [
            'source_id' => optional($source)->id,
            'category_id' => optional($fetchStartLog)->id,
            'text' =>   'Fetching started',
            'created_at'    => Carbon::now(),
        ]);

        [$donePages, $failedPages] = $this->doFetching($fetchUrl, $source);

        return response()->json($this->setSuccessResponse('Ads fetched successfully'),[
            'totalFetchedPages'  => $donePages,
            'totalFailedPages'   => $failedPages
        ]);
    }

    /**
     * @param $fetchUrl
     * @param $source
     * @return array
     */
    private function doFetching(string $fetchUrl,stdClass $source): array
    {
        $page = 1;
        $failedPages = 0;
        do {
            $done = 0;
            $headers = [
                'Content-Type'          => 'application/json',
                'Accept'                => 'application/json',
                'X-Requested-With'      => 'XMLHttpRequest'
            ];
            $parameters = ['page' => $page];
            $result = $this->sendRequest($fetchUrl, 'GET', $parameters , $headers);
            if ($result['statusCode'] == Response::HTTP_OK) {
                $result = $result['result'];
                $items = (isset($result->data)) ? json_decode($result->data) : [];
                if (empty($items))
                    continue;

                foreach ($items as $key => $item) {
                    Repo::insertRecord('ads', [
                        'source_id' => $source->id,
                        'name' => optional($item)->name,
                        'image' => optional($item)->image,
                        'link' => optional($item)->link,
                        'enable' => 1,
                        'created_at'    => Carbon::now(),
                    ]);
                }

                $firstItem = optional($items[0])->id;
                $perPage = optional($result)->perPage;
                $done = 1;
            } else {
                $failedPages++;
            }

            Repo::insertRecord('fetches', [
                'source_id' => $source->id,
                'first_item_id' => (isset($firstItem)) ? optional($firstItem)->id : null,
                'page' => $page,
                'perPage' => (isset($perPage)) ? $perPage : null,
                'done' => $done,
                'created_at'    => Carbon::now(),
            ]);

            $page = optional($result)->nextPage;
        } while (isset($page));

        return [$page, $failedPages];
    }
}
