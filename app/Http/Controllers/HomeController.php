<?php

namespace App\Http\Controllers;

use App\Repositories\Repo;
use App\Traits\HTTPRequestTrait;
use Illuminate\{Http\JsonResponse, Http\Request, Http\Response, Support\Facades\DB};
use \App\Classes\Response as myResponse ;

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
                'data'   =>   $ads
             ]
        ]);
    }

    public function fetchAds(Request $request){
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
}
