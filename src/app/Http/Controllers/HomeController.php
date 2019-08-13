<?php

namespace App\Http\Controllers;

use App\Ad;
use App\Classes\AdCollector;
use App\Classes\AdPicTransferrer;
use App\Classes\AdRedirectUrlGenerator;
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
        $basePath = explode('app/', __DIR__)[0];
        $pathToSave = $basePath . 'storage/app/public/images/ads/' . basename('003.jpg');
        dump($basePath , $pathToSave);
        $transfere = new AdPicTransferrer();
        $transferResult = $transfere->transferAdPicToCDN($pathToSave);
        dd($transferResult);
    }

    /**
     * @param Request $request
     * @param AdCollector $adResponseGenerator
     * @return JsonResponse
     */
    public function index(Request $request , AdCollector $adResponseGenerator){
        $numberOfAds    = $request->input('numberOfAds' , 6);
        $customerUUID   = $request->input('UUID');
        $sourceNames    = $request->input('source' , []);
        $urls           = $request->input('urls' , []);

        $customer = Repo::getRecords('users', ['*'], ['UUID'=>$customerUUID])->first();
        if(is_null($customer)){
            return response()->json($this->setErrorResponse(myResponse::USER_NOT_FOUND, 'User not found'));
        }

        if(is_string($sourceNames)){
            $sourceNames = convertTagStringToArray($sourceNames);
        }

        $sources = SourceRepo::getValidSource($customer->id,$sourceNames,$urls)->get();
        if($sources->isEmpty()){
            return response()->json($this->setErrorResponse(myResponse::NO_VALID_SOURCE_FOUND_FOR_CUSTOMER, 'No valid source found for this customer'));
        }

        $totalAds = $adResponseGenerator->makeAdsArray($sources, $numberOfAds);
        return response()->json($totalAds,Response::HTTP_OK , [] ,JSON_UNESCAPED_SLASHES);
    }

    public function fetchAd(Request $request){
        //ToDo : security alert : any one can update Chibekhoonam ads
        $itemID         = $request->input('item_id');
        $itemType       = $request->input('item_type');
        $sourceName     = $request->input('source');
        $source = Repo::getRecords('sources', ['*'], ['name'=>$sourceName])->first();
        $ad = Repo::getRecords('ads', ['*'], [$this->makeAdForeignId($source->id , $itemID , $itemType)])->first();

        if(is_null($ad)){
            return response()->json($this->setErrorResponse(myResponse::AD_NOT_FOUND, 'Ad not found'));
        }

        $update = DB::table('ads')->update([
           'name'   => $request->input('name' , optional($ad)->name),
           'image'  => $request->input('link' , optional($ad)->link),
           'link'   => $request->input('image' , optional($ad)->image),
        ]);

        if($update){
            return response()->json([
                'message'   =>  'ad has been updated successfully'
            ]);
        }

        return response()->json($this->setErrorResponse(myResponse::AD_UPDATE_DATABASE_ERROR, 'Database error on updating ad'));
    }

    public function adClick(Request $request , string $UUID){
        $ad = Repo::getRecords('ads' , ['*'] , ['UUID'=>$UUID])->first();
        Ad::setReferer($ad, $request->headers->get('referer'));
        if(is_null($ad)){
            return response()->json($this->setErrorResponse(myResponse::AD_NOT_FOUND, 'Ad not found'));
        }

//        (new EventTrackingHit('UA-XXXXX-Y', 555))->addParameters([
//            'ec'  => 'video',
//            'ea'  => 'play',
//            'el'  => 'holiday',
//            'ev'  =>  300
//        ])->send();

        return view('redirectForm' , ['redirectUrl'=> ( new AdRedirectUrlGenerator($ad))->generateUrl()]);
    }

    public function adTest(Request $request){
        return view('adTest');
    }
}
