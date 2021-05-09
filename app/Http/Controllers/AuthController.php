<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Http;
use App\Models\User;
use App\Models\AuthCodes;
use App\Models\Challenges;
use Carbon\Carbon;



class AuthController extends Controller
{
    public function Register(Request $request){
        $this->validate($request, [
            'firstname' => 'required',
            'lastname' => 'required',
            'role_id' => 'required',
            'company_id' => 'required',
            'email' => 'required|unique:users',
            'password' => 'required',
        ]);
        $request['password'] = Hash::make($request['password']);
        User::create($request->all());
        return response()->json(['code' => 0, 'desc' => 'successful'], 201);
    }


    public function Authenticate(Request $request){
        $challenge = $request['challenge'];
        $redirect = ''.$_ENV['PUBLIC_URL'].'auth/'.$challenge.'';
        return $redirect;
    }


    public function Login(Request $request, $challenge){
        $this->validate($request, [
            'email' => 'required',
            'password' => 'required',
        ]);
        $user = User::where('email', $request['email'])->first();
        if($user){
            if(Hash::check($request['password'], $user->password)){
                $auth_code = Crypt::encryptString(bin2hex(openssl_random_pseudo_bytes(32)));
                Challenges::create([
                   'user_id' => $user->id,
                   'challenge' => $challenge,
                ]);
                AuthCodes::create(['auth_code' => $auth_code]);
                return response()->json(['auth_code' => $auth_code]);
            }
            return ["code" => 1, "desc" => "unsuccessful: user entered wrong credentials"];
        }else{
            return ["code" => 1, "desc" => "unsuccessful: user with this E-mail does not exist"];
        }
    }



    public function Verify(Request $request)
    {
        $auth_code = AuthCodes::firstWhere('auth_code',$request['auth_code']);
        if ($auth_code->created_at->diffInSeconds(Carbon::now()) > 30) {
            return ["code" => 1, "desc" => "unsuccessful: user authorization code expired"];
        }
        $encoded_verifier = base64_encode($request['verifier']);
        $user_challenge = Challenges::where('challenge', $encoded_verifier)->first();
        if ($user_challenge->challenge == $encoded_verifier) {
            $user = User::find($user_challenge->user_id);
            $token = $user->createToken('Laravel Password Grant Client');
            $response = ["code" => 0, "desc" => "successful", "data" => [
                'acceess_token' => $token->accessToken, 'expires_at' => $token->token['expires_at'], 'user' => $user
            ]];
            $auth_code->delete();
            $user_challenge->delete();
            return $response;
        }else{
            return ["code" => 1, "desc" => "unsuccessful: user code verifier does not match code challenge"];
        }
    }

}
