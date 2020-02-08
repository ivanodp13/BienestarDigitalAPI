<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Usage;
use App\User;
use App\App;
use App\Helpers\hoursConverter;
use Firebase\JWT\JWT;
use App\Helpers\Token;
use Illuminate\Support\Facades\DB;
use DateTime;
use stdClass;

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


        //print_r($csv);
        //var_dump(file('/Applications/MAMP/htdocs/laravel-ivanodp/BienestarDigital/storage/app/usage.csv'));exit;



        $data = preg_split('/\r\n|\r|\n/', $request->data);

        $csv = array_map('str_getcsv', $data);

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

        return response()->json(
            $appsUsesLocation
        ,200);
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
        $appsIds = Usage::whereRaw("DAYOFYEAR(date) = $requestedDate")
            ->select('app_id')
            ->groupBy('app_id')
            ->get();
        $appsIds = $appsIds->toArray();

        $appsIdList = array();
        for ($i=0; $i <= (count($appsIds))-1; $i++) {

            $var = $appsIds[$i]["app_id"];
            array_push($appsIdList , $var);
        }

        $appsIds = DB::table('apps')
            ->select('id')
            ->get();
            $appsIds = $appsIds->toArray();

        $todayUse = array(); //creación del Array
        $laps = count($appsIdList); //Numero de apps a contar
        $laps = round($laps);
        $totaluse = 0;
        $appsUseList = array();
        $nonUsedApps = array();


        for ($i=1; $i < count($appsIds)+1; $i++) {
            $UsedApps = Usage::whereRaw("DAYOFYEAR(date) = $requestedDate")
            ->join('apps', 'apps.id', '=', 'usages.app_id')
            ->select('date', 'event', 'app_id', 'apps.name')
            ->where('app_id', '=', $i)
            ->groupBy('app_id')
            ->get();
            $UsedApps = $UsedApps->toArray();

            if(empty($UsedApps)==TRUE){
                array_push($nonUsedApps, $i);
            }
        }

        $appsName = DB::table('apps')
        ->select('name', 'id','icon')
        ->get();
        $appsName = $appsName->toArray();

        foreach ($appsIdList as $loop) {
            $app = new stdClass();
          

            //Obtención de los registros del dia de hoy de la app que toca
            $appsUses = Usage::whereRaw("DAYOFYEAR(date) = $requestedDate")
            ->join('apps', 'apps.id', '=', 'usages.app_id')
            ->select('date', 'event', 'app_id', 'apps.name')
            ->where('app_id', '=', $loop)
            ->get();
            $appsUses = $appsUses->toArray();

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
            ->where('app_id', '=', $loop)
            ->latest('date')
            ->first();

            /* $appsName = DB::table('apps')
            ->select('name', 'id','icon')
            ->get();
            $appsName = $appsName->toArray(); */

            if ($lastevent->event == "opens") {
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
            $appRestriction = DB::table('restrictions')
            ->select('MaxTime')
            ->where('app_id', '=', $loop)
            ->first();

            if ($appRestriction == NULL) {
                $app->maxTime = "0";
            } else{
                $str_time = $appRestriction->MaxTime;

                $str_time = preg_replace("/^([\d]{1,2})\:([\d]{2})$/", "00:$1:$2", $str_time);

                sscanf($str_time, "%d:%d:%d", $hours, $minutes, $seconds);

                $time_seconds = $hours * 3600 + $minutes * 60 + $seconds;

                $app->maxTime =  strval($time_seconds);
            }

            $app->id = ($appsName[($app_id)-1]->id);
            $app->name = ($appsName[($app_id)-1]->name);
            $app->icon = ($appsName[($app_id)-1]->icon);
            $app->seconds = strval($totaluse);
            $transformation = new hoursConverter();
            $transformation = $transformation->transform($totaluse);
            $app->use = $transformation;

            $todayUse = $app;
            array_push($appsUseList, $todayUse);
        }
        foreach ($nonUsedApps as $loop) {
            $app = new stdClass();

            $app->maxTime = "0";
            $app->id = ($appsName[$loop-1]->id);
            $app->name = ($appsName[$loop-1]->name);
            $app->icon = ($appsName[$loop-1]->icon);
            $app->use = "Sin usar hoy";
            $app->seconds = "0";

            $todayUse = $app;
            array_push($appsUseList, $todayUse);
        }

        return response()->json(
            $appsUseList
        ,200);


    }

    public function showAllAppUseThisWeek(Request $request)
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
        $requestedDate = $requestedDate->format('W')-1;
        //Obetención del número e ids de las apps a calcular
        $appsIds = Usage::whereRaw("WEEK(date) = $requestedDate")
            ->select('app_id')
            ->groupBy('app_id')
            ->get();
        $appsIds = $appsIds->toArray();

        $appsIdList = array();
        for ($i=0; $i <= (count($appsIds))-1; $i++) {

            $var = $appsIds[$i]["app_id"];
            array_push($appsIdList , $var);
        }

        $appsIds = DB::table('apps')
        ->select('id')
        ->get();
        $appsIds = $appsIds->toArray();

        $todayUse = array(); //creación del Array
        $laps = count($appsIdList); //Numero de apps a contar
        $laps = round($laps);
        $totaluse = 0;
        $appsUseList = array();

        $nonUsedApps = array();

        for ($i=1; $i < count($appsIds)+1; $i++) {
            $UsedApps = Usage::whereRaw("WEEK(date) = $requestedDate")
            ->join('apps', 'apps.id', '=', 'usages.app_id')
            ->select('date', 'event', 'app_id', 'apps.name')
            ->where('app_id', '=', $i)
            ->groupBy('app_id')
            ->get();
            $UsedApps = $UsedApps->toArray();

            if(empty($UsedApps)==TRUE){
                array_push($nonUsedApps, $i);
            }
        }

        $appsName = DB::table('apps')
        ->select('name', 'id','icon')
        ->get();
        $appsName = $appsName->toArray();


        foreach ($appsIdList as $loop) {
            $app = new stdClass();
            //Obtención de los registros del dia de hoy de la app que toca
            $appsUses = Usage::whereRaw("WEEK(date) = $requestedDate")
            ->join('apps', 'apps.id', '=', 'usages.app_id')
            ->select('date', 'event', 'app_id', 'apps.name')
            ->where('app_id', '=', $loop)
            ->get();
            $appsUses = $appsUses->toArray();

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
            ->where('app_id', '=', $loop)
            ->latest('date')
            ->first();

            /* $appsName = DB::table('apps')
            ->select('name', 'id','icon')
            ->get();
            $appsName = $appsName->toArray(); */

            if ($lastevent->event == "opens") {
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
            $app->id = ($appsName[($app_id)-1]->id);
            $app->name = ($appsName[($app_id)-1]->name);
            $app->icon = ($appsName[($app_id)-1]->icon);
            $app->seconds = strval($totaluse);
            $transformation = new hoursConverter();
            $transformation = $transformation->transform($totaluse);
            $app->use = $transformation;

            $todayUse = $app;
            array_push($appsUseList, $todayUse);
        }

        foreach ($nonUsedApps as $loop) {
            $app = new stdClass();

            $app->id = ($appsName[$loop-1]->id);
            $app->name = ($appsName[$loop-1]->name);
            $app->icon = ($appsName[$loop-1]->icon);
            $app->use = "Sin usar hoy";
            $app->seconds = "0";

            $todayUse = $app;
            array_push($appsUseList, $todayUse);
        }

        return response()->json(
            $appsUseList
        ,200);


    }

    public function showAllAppUseThisMonth(Request $request)
    {
        //Validación de token
        $request_token = $request->header('Authorization');
        $token = new token();
        $decoded_token = $token->decode($request_token);
        $user_email = $decoded_token->email;
        $user = User::where('email', '=', $user_email)->first();
        $user_id = $user->id;

        //Obtención de la fecha actual
        $requestedDate = New DateTime('2020-01-09');
        //Expresión en días del año
        $requestedDate = $requestedDate->format('m');
        //Obetención del número e ids de las apps a calcular
        $appsIds = Usage::whereRaw("MONTH(date) = $requestedDate")
            ->select('app_id')
            ->groupBy('app_id')
            ->get();
        $appsIds = $appsIds->toArray();

        $appsIdList = array();
        for ($i=0; $i <= (count($appsIds))-1; $i++) {

            $var = $appsIds[$i]["app_id"];
            array_push($appsIdList , $var);
        }

        $appsIds = DB::table('apps')
            ->select('id')
            ->get();
            $appsIds = $appsIds->toArray();

        $todayUse = array(); //creación del Array
        $laps = count($appsIdList); //Numero de apps a contar
        $laps = round($laps);
        $totaluse = 0;
        $appsUseList = array();
        $nonUsedApps = array();

        for ($i=1; $i < count($appsIds)+1; $i++) {
            $UsedApps = Usage::whereRaw("MONTH(date) = $requestedDate")
            ->join('apps', 'apps.id', '=', 'usages.app_id')
            ->select('date', 'event', 'app_id', 'apps.name')
            ->where('app_id', '=', $i)
            ->groupBy('app_id')
            ->get();
            $UsedApps = $UsedApps->toArray();

            if(empty($UsedApps)==TRUE){
                array_push($nonUsedApps, $i);
            }
        }

        $appsName = DB::table('apps')
        ->select('name', 'id','icon')
        ->get();
        $appsName = $appsName->toArray();

        foreach ($appsIdList as $loop) {
            $app = new stdClass();
            //Obtención de los registros del dia de hoy de la app que toca
            $appsUses = Usage::whereRaw("MONTH(date) = $requestedDate")
            ->join('apps', 'apps.id', '=', 'usages.app_id')
            ->select('date', 'event', 'app_id', 'apps.name')
            ->where('app_id', '=', $loop)
            ->get();
            $appsUses = $appsUses->toArray();

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
            ->where('app_id', '=', $loop)
            ->latest('date')
            ->first();

            /* $appsName = DB::table('apps')
            ->select('name', 'id','icon')
            ->get();
            $appsName = $appsName->toArray(); */


            if ($lastevent->event == "opens") {
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
            $app->id = ($appsName[($app_id)-1]->id);
            $app->name = ($appsName[($app_id)-1]->name);
            $app->icon = ($appsName[($app_id)-1]->icon);
            $app->seconds = strval($totaluse);
            $transformation = new hoursConverter();
            $transformation = $transformation->transform($totaluse);
            $app->use = $transformation;

            $todayUse = $app;
            array_push($appsUseList, $todayUse);
        }
        foreach ($nonUsedApps as $loop) {
            $app = new stdClass();

            $app->id = ($appsName[$loop-1]->id);
            $app->name = ($appsName[$loop-1]->name);
            $app->icon = ($appsName[$loop-1]->icon);
            $app->use = "Sin usar hoy";
            $app->seconds = "0";

            $todayUse = $app;
            array_push($appsUseList, $todayUse);
        }

        return response()->json(
            $appsUseList
        ,200);
    }

    public function appUseDetails(Request $request, $appName)
    {
        $request_token = $request->header('Authorization');
        $token = new token();
        $decoded_token = $token->decode($request_token);
        $user_email = $decoded_token->email;
        $user = User::where('email', '=', $user_email)->first();

        $requestedAppId = DB::table('apps')
        ->select('id')
        ->where('name', '=', $appName)
        ->get();
        $requestedAppId = $requestedAppId->toArray();

        $requestedInfo = DB::table('apps')
        ->select('id', 'name', 'icon')
        ->where('id', '=', $requestedAppId[0]->id)
        ->get();
        $requestedInfo = $requestedInfo->toArray();

        $app = new stdClass();
        $requestedAppDetails = array();

        $app->id = (strval($requestedInfo[0]->id));
        $app->name = ($requestedInfo[0]->name);
        $app->icon = ($requestedInfo[0]->icon);

        $info = $app;
        //array_push($requestedAppDetails, $info);

        //////////////////////////////////////////////////////////////////////////////////
        for ($i=0; $i <= 2; $i++) {
            //$app = new stdClass();
            //Obtención de la fecha actual
            $requestedDate = New DateTime();
            //Expresión en días del año
            $requestedDate = $requestedDate->format('z')+1;
            $requestedDate = $requestedDate-$i;

            $appUses = Usage::whereRaw("DAYOFYEAR(date) = $requestedDate")
                ->join('apps', 'apps.id', '=', 'usages.app_id')
                ->select('date', 'event', 'app_id', 'apps.name')
                ->where('app_id', '=', $requestedAppId[0]->id)
                ->get();
            $appUses = $appUses->toArray();

            if(empty($appUses)==true){
                $totaluse = "Sin uso";

                switch ($i){
                    case 0:
                        $app->todayUse = ($totaluse);
                        $info = $app;
                        //array_push($requestedAppDetails, $info);
                        break;

                    case 1:
                        $app->yesterdayUse = ($totaluse);
                        $info = $app;
                        //array_push($requestedAppDetails, $info);
                        break;

                    case 2:
                        $app->BYUse = ($totaluse);
                        $info = $app;
                        //array_push($requestedAppDetails, $info);
                        break;

                }
            }else{
                $appsUsesLength = count($appUses); //Numero de registros a calcular
                $appsUsesLength = $appsUsesLength/2; //Numero de operaciones a realizar
                $appsUsesLength = round($appsUsesLength);
                $var1 = 0;
                $var2 = 1;
                $totaluse = 0;

                $lastevent = DB::table('usages')
                    ->join('apps', 'apps.id', '=', 'usages.app_id')
                    ->select('date', 'event', 'app_id', 'apps.name')
                    ->where('app_id', '=', $requestedAppId[0]->id)
                    ->latest('date')
                    ->first();



                if ($appUses[count($appUses)-1]["event"] == "opens") {
                    for ($operations = 1; $operations <= $appsUsesLength-1 ; $operations++) {
                        $date1 = new DateTime($appUses[$var1]["date"]);
                        $date2 = new DateTime($appUses[$var2]["date"]);
                        $diff = $date2->getTimestamp() - $date1->getTimestamp();

                        $totaluse = $totaluse + $diff;
                        $var1 += 2;
                        $var2 += 2;
                    }
                    $date1 = new DateTime($appUses[$var1]["date"]);

                    $date2 = new DateTime($appUses[$var1]["date"]);
                    $date2->setTime(00, 00, 00);
                    $date2->modify('+1 day');

                    $diff = $date2->getTimestamp() - $date1->getTimestamp();

                    $totaluse += $diff;

                }else if($appUses[0]["event"] == "closes") {
                    print("Ha entrado");
                    $date1 = new DateTime($appUses[$var1]["date"]);

                    $date2 = new DateTime($appUses[$var1]["date"]);
                    $date2->setTime(00, 00, 00);

                    $diff = $date1->getTimestamp() - $date2->getTimestamp();

                    $totaluse += $diff;
                    for ($operations = 1; $operations <= $appsUsesLength-1 ; $operations++) {
                        $date1 = new DateTime($appUses[($var1)+1]["date"]);
                        $date2 = new DateTime($appUses[($var2)+1]["date"]);
                        $diff = $date2->getTimestamp() - $date1->getTimestamp();

                        $totaluse = $totaluse + $diff;
                        $var1 += 2;
                        $var2 += 2;
                    }
                }else{
                    for ($operations = 1; $operations <= $appsUsesLength ; $operations++) {
                        $date1 = new DateTime($appUses[$var1]["date"]);
                        $date2 = new DateTime($appUses[$var2]["date"]);
                        $diff = $date2->getTimestamp() - $date1->getTimestamp();

                        $totaluse = $totaluse + $diff;
                        $var1 += 2;
                        $var2 += 2;
                    }
                }
                switch ($i){
                    case 0:
                        $transformation = new hoursConverter();
                        $transformation = $transformation->transform($totaluse);
                        $app->todayUse = $transformation;
                        $info = $app;
                        //array_push($requestedAppDetails, $info);
                        break;

                    case 1:
                        $transformation = new hoursConverter();
                        $transformation = $transformation->transform($totaluse);
                        $app->yesterdayUse = $transformation;
                        $info = $app;
                        //array_push($requestedAppDetails, $info);
                        break;

                    case 2:
                        $transformation = new hoursConverter();
                        $transformation = $transformation->transform($totaluse);
                        $app->BYUse = $transformation;
                        $info = $app;
                        //array_push($requestedAppDetails, $info);
                        break;

                }
            }


        }
        //////////////////////////////////////////////////////
        //$appTotal = new stdClass();
        $appsUses = DB::table('usages')
        ->join('apps', 'apps.id', '=', 'usages.app_id')
        ->select('date', 'event', 'apps.name')
        ->where('app_id', '=', $requestedAppId[0]->id)
        ->get();

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

        $transformation = new hoursConverter();
        $transformation = $transformation->transform($totaluse);
        $app->TotalUse = $transformation;
        $info = $app;
        array_push($requestedAppDetails, $info);

        return response()->json(
            $requestedAppDetails
        ,200);

    }
}
