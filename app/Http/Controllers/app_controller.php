<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\App;

class app_controller extends Controller
{
    public function import(Request $request)
    {
        $data = preg_split('/\r\n|\r|\n/', $request->data);

        $csv = array_map('str_getcsv', $data);

        $array_num = count($csv);
        for ($i = 1; $i < $array_num; ++$i){

            $appname = $csv[$i][0];
            $requestedapp = App::where('name', '=', $appname)->first();
            //var_dump($requestedapp);exit;
            if ($requestedapp == NULL)
            {
                $app = new App();
                $app->name = $appname;
                $app->icon = $csv[$i][1];
                $app->save();

            }else if (($requestedapp->name) == $appname)
            {
                echo 'La app '.$appname .' ya se encuentra importada'."\n";
            }else{
                $app = new App();
                $app->name = $appname;
                $app->icon = $csv[$i][1];
                $app->save();
            }

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
