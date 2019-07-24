<?php

namespace App\Http\Controllers;

use App\Classes\AdLinkGenerator;
use App\Repositories\Repo;
use App\Repositories\SourceRepo;
use App\Traits\HTTPRequestTrait;
use Illuminate\{Contracts\Pagination\LengthAwarePaginator,
    Http\JsonResponse,
    Http\Request,
    Http\Response,
    Support\Collection,
    Support\Facades\DB};
use \App\Classes\Response as myResponse ;

class HomeController extends Controller
{
    use HTTPRequestTrait;

    public function debug(Request $request){
        //
    }

    /**
     * @param Request $request
     * @param AdLinkGenerator $adLinkGenerator
     * @return JsonResponse
     */
    public function index(Request $request , AdLinkGenerator $adLinkGenerator){
        $numberOfAds = $request->get('numberOfAds' , 6);
        $customerUUID = $request->get('UUID');
        $sourceNames = $request->get('source' , []);
        $customer = Repo::getRecords('users', ['*'], ['UUID'=>$customerUUID])->first();
        if(!isset($customer)){
            return response()->json($this->setErrorResponse(myResponse::USER_NOT_FOUND, 'User not found'), Response::HTTP_NOT_FOUND);
        }

        if(is_string($sourceNames)){
            $sourceNames = convertTagStringToArray($sourceNames);
        }

        $sources = SourceRepo::getValidSource($customer->id,$sourceNames)->get();

        if($sources->isEmpty()){
            return response()->json($this->setErrorResponse(myResponse::NO_VALID_SOURCE_FOUND_FOR_CUSTOMER, 'NO valid source found for this customer'), Response::HTTP_NOT_FOUND);
        }

        $totalAds = $this->makeAdsArray($adLinkGenerator, $sources, $numberOfAds);
        return response()->json($totalAds,Response::HTTP_OK , [] ,JSON_UNESCAPED_SLASHES);
    }

    public function fetchAd(Request $request){
        //ToDo : security alert : any one can update Chibekhoonam ads
        $itemID = $request->get('item_id');
        $sourceName = $request->get('source');
        $source = Repo::getRecords('sources', ['*'], ['name'=>$sourceName])->first();
        //ToDo Hard Code
        $foreignID = 's'.$source->id.'_'.$itemID;
        $ad = Repo::getRecords('ads', ['*'], [$foreignID])->first();

        if(!isset($ad)){
            return response()->json($this->setErrorResponse(myResponse::AD_NOT_FOUND, 'Ad not found'), Response::HTTP_NOT_FOUND);
        }

        $update = DB::table('ads')->update([
           'name'   => $request->get('name' , optional($ad)->name),
           'image'  => $request->get('link' , optional($ad)->link),
           'link'   => $request->get('image' , optional($ad)->image),
        ]);

        if($update){
            return response()->json([
                'message'   =>  'ad has been updated successfully'
            ]);
        }else{
            return response()->json($this->setErrorResponse(myResponse::AD_UPDATE_DATABASE_ERROR, 'Database error on updating ad'), Response::HTTP_SERVICE_UNAVAILABLE);
        }
    }

    public function adClick(Request $request, string $UUID){
        $ad = Repo::getRecords('ads' , ['*'] , ['UUID'=>$UUID])->first();
        if(!isset($ad)){
            return response()->json($this->setErrorResponse(myResponse::AD_NOT_FOUND, 'Ad not found'), Response::HTTP_NOT_FOUND);
        }
        return redirect($ad->link);
    }

    /**
     * @param AdLinkGenerator $adLinkGenerator
     * @param LengthAwarePaginator $ads
     */
    private function generateAdLinks(AdLinkGenerator $adLinkGenerator, LengthAwarePaginator $ads): void
    {
        foreach ($ads as $ad) {
            $adLinkGenerator->setAd($ad);
            $adLinkGenerator->generateLink();
        }
    }

    /**
     * @param AdLinkGenerator $adLinkGenerator
     * @param Collection $sources
     * @param $numberOfAds
     * @return array
     */
    private function makeAdsArray(AdLinkGenerator $adLinkGenerator, Collection $sources, $numberOfAds): array
    {
        $totalAds = [];
        foreach ($sources as $source) {
            $ads = Repo::getRecords('ads', ['UUID', 'name', 'link', 'image'], ['source_id' => $source->id, 'enable' => 1]);
            $ads = $ads->paginate($numberOfAds, ['*'], 'page');
            $this->generateAdLinks($adLinkGenerator, $ads);
            $totalAds[] = [
                'title' => $source->display_name,
                'color' => 'white',
                'icon' => 'icon',
                'data' => $ads
            ];
        }
        return $totalAds;
    }
}
