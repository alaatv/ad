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
        $customer = Repo::getRecords('users', ['*'], ['UUID'=>$customerUUID])->first();
        if(!isset($customer)){
            return response()->json($this->setErrorResponse(myResponse::USER_NOT_FOUND, 'User not found'), Response::HTTP_NOT_FOUND);
        }

        if($request->has('source')){
            $sourcesName = explode(",", $request->get('source'));
            $sourcesName = array_filter($sourcesName);

            $sources = SourceRepo::getValidSourceViaUser($sourcesName, $customer->id)->get();
        }else{
            $sources = SourceRepo::getValidSourceViaContract($customer)->get();
        }

        if($sources->isEmpty()){
            return response()->json($this->setErrorResponse(myResponse::NO_VALID_SOURCE_FOUND_FOR_CUSTOMER, 'NO valid source found for this customer'), Response::HTTP_NOT_FOUND);
        }

        $totalAds = $this->makeAdsArray($adLinkGenerator, $sources, $numberOfAds);
        return response()->json($totalAds,Response::HTTP_OK , [] ,JSON_UNESCAPED_SLASHES);
    }

    public function fetchAd(Request $request){
        //ToDo : security : any one can update Chibekhoonam ads
        $ad = Repo::getRecords('ads', ['*'], ['foreign_id'=>$request->get('ad_id')])->first();

        if(!isset($ad)){
            return response()->json($this->setErrorResponse(myResponse::AD_NOT_FOUND, 'Ad not found'), Response::HTTP_NOT_FOUND);
        }

        $update = DB::table('ads')->update([
           'name'   => $request->get('name' , $ad->name),
           'image'   => $request->get('link' , $ad->link),
           'link'   => $request->get('image' , $ad->image),
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
