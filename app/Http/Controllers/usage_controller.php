<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Usage;
use App\User;
use App\App;
use Firebase\JWT\JWT;
use App\Helpers\Token;
use Illuminate\Support\Facades\DB;
use DateTime;

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
            $usage->event = $csv[$i][2];
            $usage->latitude = $csv[$i][3];
            $usage->longitude = $csv[$i][4];
            $currentappname = $csv[$i][1];
            $currentapp = App::where('name', '=', $currentappname)->first();
            $usage->user_id = $user_id;
            $usage->app_id = $currentapp->id;

            $usage->save();
        }

        return response()->json([
            "message" => 'Importación realizada con éxito'
        ],200);
    }
    
    public function showUseLocations(Request $request)
    {
        $request_token = $request->header('Authorization');
        $token = new token();
        $decoded_token = $token->decode($request_token);
        $user_email = $decoded_token->email;
        $user = User::where('email', '=', $user_email)->first();
        $user_id = $user->id;

        $appsUsesLocation = DB::table('usages')
        ->select('latitude', 'longitude')
        ->distinct()
        ->get();
        
        return response()->json([
            $appsUsesLocation
        ],200);
    }

    public function showAppLocations(Request $request, $id)
    {
        $request_token = $request->header('Authorization');
        $token = new token();
        $decoded_token = $token->decode($request_token);
        $user_email = $decoded_token->email;
        $user = User::where('email', '=', $user_email)->first();
        $user_id = $user->id;

        $appsUsesLocation = DB::table('usages')
        ->join('apps', 'apps.id', '=', 'usages.app_id')
        ->select('latitude', 'longitude', 'apps.name')
        ->where('app_id', '=', $id)
        ->distinct()
        ->get();
        
        return response()->json([
            $appsUsesLocation
        ],200);
    }

    public function showLastUsesLocations(Request $request)
    {
        $request_token = $request->header('Authorization');
        $token = new token();
        $decoded_token = $token->decode($request_token);
        $user_email = $decoded_token->email;
        $user = User::where('email', '=', $user_email)->first();
        $user_id = $user->id;

        $appsLastUsesLocation = DB::table('usages')
        ->join('apps', 'apps.id', '=', 'usages.app_id')
        ->select('app_id', 'latitude', 'longitude', 'apps.name')
        ->where('event', '=', "closes")
        ->groupBy('app_id')
        ->get();
        
        return response()->json([
            $appsLastUsesLocation
        ],200);
    }

    public function showAppUse(Request $request, $id)
    {
        $request_token = $request->header('Authorization');
        $token = new token();
        $decoded_token = $token->decode($request_token);
        $user_email = $decoded_token->email;
        $user = User::where('email', '=', $user_email)->first();
        $user_id = $user->id;

        $requestedDate = New DateTime($request->date);
        $requestedDate = $requestedDate->format('z')+1;

        $appsUses = Usage::whereRaw("DAYOFYEAR(date) = $requestedDate")
        ->select('date', 'event')
        ->where('app_id', '=', $id)
        ->get();

        
        $appName = DB::table('apps')
        ->select('name')
        ->where('id', '=', $id)
        ->get();
        $appName = $appName->toArray();
        $appName = $appName[0]->name;

        $appsUses = $appsUses->toArray();

        $var1 = 0;
        $var2 = 1;
        $count = count($appsUses);
        $laps = $count/2;
        $laps = round($laps);
        $totaluse = 0;
        
        if(empty($appsUses) == true){
            return response()->json([
                "message" => 'La app '."$appName".' no se ha usado el día seleccionado'
            ],200);
        }

        if($appsUses[$count-1]["event"] == "opens"){
            //var_dump($laps);exit;
            for ($operations = 1; $operations <= $laps-1 ; $operations++) {
                $date1 = new DateTime($appsUses[$var1]["date"]);
                $date2 = new DateTime($appsUses[$var2]["date"]);
                $diff = $date2->getTimestamp() - $date1->getTimestamp();
                
                $totaluse = $totaluse + $diff;
                $var1 += 2;
                $var2 += 2;
            }
            $date1 = new DateTime($appsUses[$var1]["date"]);
            
            $date2 = new DateTime($appsUses[$var1]["date"]);
            $date2->setTime(00, 00, 00);
            $date2->modify('+1 day');
            
            $diff = $date2->getTimestamp() - $date1->getTimestamp();
            
            $totaluse += $diff;

        }else if ($appsUses[0]["event"] == "closes"){
            $date1 = new DateTime($appsUses[$var1]["date"]);
            
            $date2 = new DateTime($appsUses[$var1]["date"]);
            $date2->setTime(00, 00, 00);
            
            $diff = $date1->getTimestamp() - $date2->getTimestamp();
            
            $totaluse += $diff;
            for ($operations = 1; $operations <= $laps-1 ; $operations++) {
                $date1 = new DateTime($appsUses[($var1)+1]["date"]);
                $date2 = new DateTime($appsUses[($var2)+1]["date"]);
                $diff = $date2->getTimestamp() - $date1->getTimestamp();
                
                $totaluse = $totaluse + $diff;
                $var1 += 2;
                $var2 += 2;
            }
        }else{
            for ($operations = 1; $operations <= $laps ; $operations++) {
                $date1 = new DateTime($appsUses[$var1]["date"]);
                $date2 = new DateTime($appsUses[$var2]["date"]);
                $diff = $date2->getTimestamp() - $date1->getTimestamp();
                
                $totaluse = $totaluse + $diff;
    
                $var1 += 2;
                $var2 += 2;
            }
        }

        return response()->json([
            "message" => 'La app '."$appName".' se ha usado '."$totaluse".' segundos el día seleccionado.'
        ],200);

    }

    public function showAllAppUseToday(Request $request)
    {
        //Validación de token
        $request_token = $request->header('Authorization');
        $token = new token();
        $decoded_token = $token->decode($request_token);
        $user_email = $decoded_token->email;
        $user = User::where('email', '=', $user_email)->first();
        $user_id = $user->id;

        //Obtención de la fecha actual
        $requestedDate = New DateTime();
        //Expresión en días del año
        $requestedDate = $requestedDate->format('z')+1;

        //Obetención del número e ids de las apps a calcular
        $appsIdList = DB::table('apps')
        ->select('id')
        ->get();
        $appsIdList = $appsIdList->toArray();

        $todayUse = array(); //creación del Array
        $laps = count($appsIdList); //Numero de apps a contar
        $laps = round($laps);
        $totaluse = 0;

        for ($appNumber=1; $appNumber < $laps+1 ; $appNumber++) { 
             //Obtención de los registros del dia de hoy de la app que toca
            $appsUses = Usage::whereRaw("DAYOFYEAR(date) = $requestedDate")
            ->join('apps', 'apps.id', '=', 'usages.app_id')
            ->select('date', 'event', 'app_id', 'apps.name')
            ->where('app_id', '=', $appNumber)
            ->get();
            $appsUses = $appsUses->toArray();
            //echo "App número ".$appNumber." | "."\n";

            
            $appsUsesLength = count($appsUses); //Numero de registros a calcular
            $appsUsesLength = $appsUsesLength/2; //Numero de operaciones a realizar
            $appsUsesLength = round($appsUsesLength);
            $var1 = 0;
            $var2 = 1;

            $app_id = $appsUses[0]["app_id"]; // id de la app
            $totaluse = 0;

            $lastevent = DB::table('usages')
            ->join('apps', 'apps.id', '=', 'usages.app_id')
            ->select('date', 'event', 'app_id', 'apps.name')
            ->where('app_id', '=', $appNumber)
            ->latest('date')
            ->first();

            if ($lastevent->event == "opens") {
                echo "El ".$appNumber." entra en el 1º if"."\n";
                for ($operations = 1; $operations <= $appsUsesLength-1 ; $operations++) {
                    $date1 = new DateTime($appsUses[$var1]["date"]);
                    $date2 = new DateTime($appsUses[$var2]["date"]);
                    $diff = $date2->getTimestamp() - $date1->getTimestamp();
                    
                    $totaluse = $totaluse + $diff;
                    $var1 += 2;
                    $var2 += 2;
                }
                $date1 = new DateTime($appsUses[$var1]["date"]);
                
                $date2 = new DateTime($appsUses[$var1]["date"]);
                $date2->setTime(00, 00, 00);
                $date2->modify('+1 day');
                
                $diff = $date2->getTimestamp() - $date1->getTimestamp();
                
                $totaluse += $diff;

            }else if($appsUses[0]["event"] == "closes") {
                $date1 = new DateTime($appsUses[$var1]["date"]);
                
                $date2 = new DateTime($appsUses[$var1]["date"]);
                $date2->setTime(00, 00, 00);
                
                $diff = $date1->getTimestamp() - $date2->getTimestamp();
                
                $totaluse += $diff;
                for ($operations = 1; $operations <= $appsUsesLength-1 ; $operations++) {
                    $date1 = new DateTime($appsUses[($var1)+1]["date"]);
                    $date2 = new DateTime($appsUses[($var2)+1]["date"]);
                    $diff = $date2->getTimestamp() - $date1->getTimestamp();
                    
                    $totaluse = $totaluse + $diff;
                    $var1 += 2; 
                    $var2 += 2;
                }
            }else{
                for ($operations = 1; $operations <= $appsUsesLength ; $operations++) {
                    $date1 = new DateTime($appsUses[$var1]["date"]);
                    $date2 = new DateTime($appsUses[$var2]["date"]);
                    $diff = $date2->getTimestamp() - $date1->getTimestamp();
                    
                    $totaluse = $totaluse + $diff;
                    $var1 += 2;
                    $var2 += 2;
                }
            }
            $todayUse[$app_id] = $totaluse;
        }
            
        return response()->json([
            $todayUse
        ],200);


    }

    public function showAllTimeAppUse(Request $request, $id)
    {
        $request_token = $request->header('Authorization');
        $token = new token();
        $decoded_token = $token->decode($request_token);
        $user_email = $decoded_token->email;
        $user = User::where('email', '=', $user_email)->first();
        $user_id = $user->id;

        $appsUses = DB::table('usages')
        ->join('apps', 'apps.id', '=', 'usages.app_id')
        ->select('date', 'event', 'apps.name')
        ->where('app_id', '=', $id)
        ->get();

        $appName = DB::table('apps')
        ->select('name')
        ->where('id', '=', $id)
        ->get();
        $appName = $appName->toArray();
        $appName = $appName[0]->name;

        $appsUses = $appsUses->toArray();

        $var1 = 0;
        $var2 = 1;
        $count = count($appsUses);
        $laps = $count/2;
        $totaluse = 0;

        for ($i = 1; $i <= $laps ; $i++) {
            $date1 = new DateTime($appsUses[$var1]->date);
            $date2 = new DateTime($appsUses[$var2]->date);
            $diff = $date2->getTimestamp() - $date1->getTimestamp();
            
            $totaluse = $totaluse + $diff;
            $var1 += 2;
            $var2 += 2;
        }

        return response()->json([
            "message" => 'La app '."$appName".' se ha usado '."$totaluse".' segundos, desde que se instaló.'
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
