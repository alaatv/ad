<?php

namespace App\Http\Controllers;

use App\Classes\AdLinkGenerator;
use App\Repositories\Repo;
use App\Traits\HTTPRequestTrait;
use Carbon\Carbon;
use Illuminate\{Contracts\Pagination\LengthAwarePaginator,
    Database\Query\Builder,
    Http\JsonResponse,
    Http\Request,
    Http\Response,
    Support\Facades\DB};
use \App\Classes\Response as myResponse ;

class HomeController extends Controller
{
    use HTTPRequestTrait;

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

            $sources = DB::table('sources')
                ->join('contracts', 'sources.id', '=', 'contracts.source_id')
                ->whereIn('sources.name' , $sourcesName)
                ->where('sources.enable' , 1)
                ->where('contracts.user_id' , $customer->id)
                ->where(function (Builder $q){
                    $q ->where('contracts.since', '<=', Carbon::createFromFormat('Y-m-d H:i:s', Carbon::now('Asia/Tehran')))
                        ->orWhereNull('contracts.since');
                })->where(function (Builder $q) {
                    $q->where('contracts.till', '>=', Carbon::createFromFormat('Y-m-d H:i:s', Carbon::now('Asia/Tehran')))
                        ->orWhereNull('contracts.till');
                })
                ->select('*')
                ->get();
        }else{
            $sources = DB::table('contracts')
                ->join('sources', 'sources.id', '=', 'contracts.source_id')
                ->where('sources.enable' , 1)
                ->where('contracts.user_id' , $customer->id)
                ->where(function (Builder $q){
                    $q ->where('contracts.since', '<=', Carbon::createFromFormat('Y-m-d H:i:s', Carbon::now('Asia/Tehran')))
                        ->orWhereNull('contracts.since');
                })->where(function (Builder $q) {
                    $q->where('contracts.till', '>=', Carbon::createFromFormat('Y-m-d H:i:s', Carbon::now('Asia/Tehran')))
                        ->orWhereNull('contracts.till');
                })
                ->select('*')
                ->get();
        }

        if($sources->isEmpty()){
            return response()->json($this->setErrorResponse(myResponse::NO_VALID_SOURCE_FOUND_FOR_CUSTOMER, 'NO valid source found for this customer'), Response::HTTP_NOT_FOUND);
        }

        $totalAds = [];
        foreach ($sources as $source) {
            $ads = Repo::getRecords('ads' , ['UUID' , 'name' , 'link' , 'image'],['source_id'=>$source->id,'enable'=>1]);
            $ads = $ads->paginate($numberOfAds, ['*'], 'page');
            $this->generateAdLinks($adLinkGenerator, $ads);
            $totalAds[] = [
                'title'  =>   $source->display_name,
                'color'  =>   'white',
                'icon'   =>   'icon',
                'data'   =>   $ads
            ];
        }


        return response()->json($totalAds,Response::HTTP_OK , [] ,JSON_UNESCAPED_SLASHES);
    }

    public function fetchAd(Request $request){
        //ToDo : security : any one can update Chibekhoonam ads
        $ad = Repo::getRecords('ads', ['*'], ['foreign_id'=>$request->ad_id])->first();

        if(!isset($ad)){
            return response()->json($this->setErrorResponse(myResponse::AD_NOT_FOUND, 'Ad not found'), Response::HTTP_NOT_FOUND);
        }

        $update = DB::table('ads')->update([
           'name'   => $request->name,
           'image'   => $request->link,
           'link'   => $request->image,
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
}
