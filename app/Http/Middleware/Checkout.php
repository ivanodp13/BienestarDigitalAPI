<?php
namespace App\Http\Middleware;
use Illuminate\Http\Request;
use App\User;
use App\Helpers\Token;
use Closure;
class checkout
{
    public function handle($request, Closure $next)
    {
        $authorization = $request->header('Authorization');
        $token = new token();
        $decoded_token = $token->decode($authorization);
        $email = $decoded_token->email;
        $data = ['email' => $email];
        $user = User::where($data)->first();
        if ($user)
        {
            return $next($request);
        }
        return response()->json([
            "message" => 'unauthorized'
        ],401);
    }
}