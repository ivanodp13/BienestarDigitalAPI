<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Restriction;
use App\User;
use App\App;
use App\Helpers\Token;

class restriction_controller extends Controller
{
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
        $request_token = $request->header('Authorization');
        $token = new token();
        $decoded_token = $token->decode($request_token);
        $user_email = $decoded_token->email;
        $user = User::where('email', '=', $user_email)->first();
        $user_id = $user->id;
        
        $app = App::where('name', '=', $request->App)->first();
        if($app == NULL)
        {
            return response()->json([
                "message" => 'La app no se encuentra en la base de datos'
            ],401);
        }

        $restriction = new Restriction();
        $restriction->user_id = $user_id;
        $restriction->app_id = $app->id;
        $restriction->MaxTime = $request->MaxTime;
        $restriction->InitTime = $request->InitTime;
        $restriction->EndTime = $request->EndTime;
        $restriction->save();

        return response()->json([
            "message" => 'Restricción para la app de '.$request->App.' añadida correctamente'
        ],401);        
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
