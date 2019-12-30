<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\App;

class app_controller extends Controller
{
    public function import()
    {       
        $csv = array_map('str_getcsv', file('/Applications/MAMP/htdocs/laravel-ivanodp/BienestarDigital/storage/app/appsData.csv'));
        //print_r($csv);

        $array_num = count($csv);
        for ($i = 0; $i < $array_num; ++$i){
            $app = new App();
            $app->name = $csv[$i][0];
            $app->icon = $csv[$i][1];
            $app->save();
        }

        return response()->json([
            "message" => 'Importación realizada con éxito'
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
