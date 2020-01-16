<?php
namespace App\Http\Controllers;
use Illuminate\Http\Request;
use App\User;
use Firebase\JWT\JWT;
use App\Helpers\Token;
use App\Helpers\passwordGenerator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;

class user_controller extends Controller
{
    public function showUserInfo(Request $request)
    {
        $request_token = $request->header('Authorization');
        $token = new token();
        $decoded_token = $token->decode($request_token);
        $user_email = $decoded_token->email;
        $user = User::select('id', 'name', 'email')->where('email', '=', $user_email)->first();

        return response()->json(
            $user
        ,200);

    }


    /**
     * Restore the passwords of the user in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function passrestore(Request $request)
    {
        $requested_email = ['email' => $request->email];
        $user = User::where($requested_email)->first();
        if($user==NULL){
            return response()->json([
                "message" => 'Ese email no existe'
            ],401);
        }

        $newPass = new PasswordGenerator();
        $newPass = $newPass->newPass();

        $user->password = encrypt($newPass);
        $user->save();

        $data = array("newPass" => $newPass);
        $subject = "Tu nueva contraseña";
        $for = $request->email;
        Mail::send('emails.forgot', $data, function($msj) use($subject,$for){
            $msj->from("bienestappPassRecovery@outlook.com","BienestApp Password Recovery");
            $msj->subject($subject);
            $msj->to($for);
        });

        return response()->json([
            "message" => 'Contraseña cambiada y enviada.'
        ],200);
    }

    /**
     * Edit the passwords of the user in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function passedit(Request $request)
    {
        $request_token = $request->header('Authorization');
        $token = new token();
        $decoded_token = $token->decode($request_token);
        $user_email = $decoded_token->email;
        $user = User::where('email', '=', $user_email)->first();
        $user_id = $user->id;

        if($request->currentPassword==NULL || $request->newPassword==NULL || $request->confirmPassword==NULL){
            return response()->json([
                "message" => 'Debes rellenar todos los campos'
            ],401);
        }

        $inputpassword = $request->currentPassword;

        if(($inputpassword == decrypt($user->password)) && ($request->newPassword == $request->confirmPassword))
        {
            $user->password = encrypt($request->newPassword);
            $user->save();

            return response()->json([
                "message" => 'Contraseña cambiada correctamente'
            ],200);
        }


        return response()->json([
            "message" => 'Alguno de los campos no coincide'
        ],401);
    }



    public function login(Request $request)
    {
        $data = ['email' => $request->email];
        $user = User::where($data)->first();
        if($user==NULL){
            return response()->json([
                "message" => 'Email o contraseña incorrecta'
            ],401);
        }
        if(decrypt($user->password) == $request->password)
        {
            $token = new token($data);
            $token = $token->encode();
            return response()->json([
                "token" => $token
            ],200);
        }
        return response()->json([
            "message" => 'Email o contraseña incorrecta'
        ],401);
    }
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $users = User::all();
        return response()->json([
            $users
        ],200);
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
        $requested_email = ['email' => $request->email];
        $email = User::where($requested_email)->first();
        if($email!=NULL){
            return response()->json([
                "message" => 'Ese email ya existe'
            ],401);
        }

        $requested_name = ['name' => $request->name];
        $name = User::where($requested_name)->first();
        if($name!=NULL){
            return response()->json([
                "message" => 'Ese usuario ya existe'
            ],401);
        }

        $user = new User();
        $user->name = $request->name;
        $user->email = $request->email;
        $user->password = encrypt($request->password);
        $user->save();
        $token = new token(['email' => $user->email]);
        $token = $token->encode();
        return response()->json([
            "token" => $token
        ],200);
        //$token = JWT::encode($data_token, $this->key);
    }
    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show(Request $request)
    {

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
        $request_token = $request->header('Authorization');
        $token = new token();
        $decoded_token = $token->decode($request_token);
        $user_email = $decoded_token->email;
        $user = User::where('email', '=', $user_email)->first();
        $user_id = $user->id;
        if($user_id!=$id){
            return response()->json([
                "message" => 'Error, solo puedes editar tu usuario'
            ],401);
        }
        if($request->name==NULL || $request->email==NULL || $request->password==NULL){
            return response()->json([
                "message" => 'Debes rellenar todos los campos'
            ],401);
        }
        $user->name = $request->name;
        $user->email = $request->email;
        $user->password = $request->password;
        $user->save();
        return response()->json([
            "message" => 'Campos actualizados'
        ],200);
    }
    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $user = User::find($id);
        $user->delete();
        return response()->json([
            "message" => 'Usuario, categorias y contraseñas eliminadas correctamente'
        ],200);
    }
}
