<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;

class HomeController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    public function index(){

        $adds = DB::table('adds')->select(['name' , 'link' , 'image'])->paginate(10, ['*'], 'adds');
//        $adds = Add::query()->select('*')->paginate(10, ['*'], 'adds');

        return response()->json([
           'title'  =>   'چی بخونم',
           'color'  =>   'example_color',
           'icon'   =>   'example_icon',
            'data'  =>   $adds
        ]);
    }
}
