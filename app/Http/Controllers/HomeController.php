<?php

namespace App\Http\Controllers;

use App\Classes\AdCollector;
use App\Repositories\Repo;
use App\Repositories\SourceRepo;
use App\Traits\adTrait;
use App\Traits\HTTPRequestTrait;
use Illuminate\{
    Http\JsonResponse,
    Http\Request,
    Http\Response,
    Support\Facades\DB};
use \App\Classes\Response as myResponse ;

class HomeController extends Controller
{
    use HTTPRequestTrait;
    use adTrait;

    public function debug(Request $request){
        dd( config('download_server.IMAGES_PARTIAL_PATH'));
    }

    /**
     * @param Request $request
     * @param AdCollector $adResponseGenerator
     * @return JsonResponse
     */
    public function index(Request $request , AdCollector $adResponseGenerator){
        $numberOfAds    = $request->get('numberOfAds' , 6);
        $customerUUID   = $request->get('UUID');
        $sourceNames    = $request->get('source' , []);
        $urls           = $request->get('urls' , []);
        $customer = Repo::getRecords('users', ['*'], ['UUID'=>$customerUUID])->first();
        if(!isset($customer)){
            return response()->json($this->setErrorResponse(myResponse::USER_NOT_FOUND, 'User not found'), Response::HTTP_NOT_FOUND);
        }

        if(is_string($sourceNames)){
            $sourceNames = convertTagStringToArray($sourceNames);
        }

        $sources = SourceRepo::getValidSource($customer->id,$sourceNames,$urls)->get();

        if($sources->isEmpty()){
            return response()->json($this->setErrorResponse(myResponse::NO_VALID_SOURCE_FOUND_FOR_CUSTOMER, 'No valid source found for this customer'), Response::HTTP_NOT_FOUND);
        }

        $totalAds = $adResponseGenerator->makeAdsArray($sources, $numberOfAds);
        return response()->json($totalAds,Response::HTTP_OK , [] ,JSON_UNESCAPED_SLASHES);
    }

    public function fetchAd(Request $request){
        //ToDo : security alert : any one can update Chibekhoonam ads
        $itemID = $request->get('item_id');
        $itemType = $request->get('item_type');
        $sourceName = $request->get('source');
        $source = Repo::getRecords('sources', ['*'], ['name'=>$sourceName])->first();
        $ad = Repo::getRecords('ads', ['*'], [$this->makeAdForeignId($source->id , $itemID , $itemType)])->first();

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

        $redirectUrl = $request->get('redirect' , $ad->link);
        return redirect($redirectUrl);
        //ToDo pass redirectUrl to an HTML form
//        return view('adForm' , compact($redirectUrl));
    }

}
