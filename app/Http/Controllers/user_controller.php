<?php
namespace App\Http\Controllers;
use Illuminate\Http\Request;
use App\User;
use Firebase\JWT\JWT;
use App\Helpers\Token;
use Illuminate\Support\Facades\DB;
class user_controller extends Controller
{
    public function login(Request $request)
    {
        $data = ['email' => $request->email];
        $user = User::where($data)->first();
        if($user==NULL){
            return response()->json([
                "message" => 'Incorrect email or password'
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
            "message" => 'Incorrect email or password'
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
                "message" => 'Ya existe un usuario con ese email'
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