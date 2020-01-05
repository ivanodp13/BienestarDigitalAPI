<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Usage;
use App\User;
use App\App;
use Firebase\JWT\JWT;
use App\Helpers\Token;
use Illuminate\Support\Facades\DB;

class usage_controller extends Controller
{
    public function import(Request $request)
    {       
        $request_token = $request->header('Authorization');
        $token = new token();
        $decoded_token = $token->decode($request_token);
        $user_email = $decoded_token->email;
        $user = User::where('email', '=', $user_email)->first();
        $user_id = $user->id;

        $csv = array_map('str_getcsv', file('/Applications/MAMP/htdocs/laravel-ivanodp/BienestarDigital/storage/app/usage.csv'));
        //print_r($csv);

        $array_num = count($csv);
        for ($i = 1; $i < $array_num; ++$i){
            $usage = new Usage();
            $usage->date = $csv[$i][0];
            $usage->time = $csv[$i][1];
            $usage->event = $csv[$i][3];
            $usage->latitude = $csv[$i][4];
            $usage->longitude = $csv[$i][5];
            $currentappname = $csv[$i][2];
            $currentapp = App::where('name', '=', $currentappname)->first();
            $usage->user_id = $user_id;
            $usage->app_id = $currentapp->id;

            $usage->save();
        }

        return response()->json([
            "message" => 'Importación realizada con éxito'
        ],200);
    }

    public function showLocations(Request $request, $id)
    {
        $request_token = $request->header('Authorization');
        $token = new token();
        $decoded_token = $token->decode($request_token);
        $user_email = $decoded_token->email;
        $user = User::where('email', '=', $user_email)->first();
        $user_id = $user->id;

        $appsuse = DB::table('usages')
        ->join('apps', 'apps.id', '=', 'usages.app_id')
        ->select('latitude', 'longitude', 'apps.name')
        ->where('app_id', '=', $id)
        ->distinct()
        ->get();
        
        return response()->json([
            $appsuse
        ],200);
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }
}
