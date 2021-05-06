<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Http;
use App\Models\User;
use App\Models\AuthCodes;



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
        $user = User::create($request->all());
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
        $email = $request['email'];
        $password = $request['password'];
        $user = User::where('email', $email)->first();
        if($user){
            if(Hash::check($password, $user->password)){
                $random = bin2hex(openssl_random_pseudo_bytes(32));
                $auth_code = Crypt::encryptString($random);
                AuthCodes::create(['auth_code' => $auth_code]);
                return ''.$_ENV['PUBLIC_URL'].'verify/'.$challenge.'/'.$auth_code.'/'.$user['id'];
            }
            return ["code" => 1, "desc" => "unsuccessful"];
        }else{
            return ["code" => 1, "desc" => "unsuccessful"];
        }
    }



    public function Verify(Request $request, $challenge, $auth_code, $id){
        $verifier = $request['verifier'];
        $user = User::find($id);
        $retrieveAuthCode = AuthCodes::where('auth_code', $auth_code)->first();
        if(base64_decode($challenge) == $verifier && $retrieveAuthCode->expired == false){
            $token = $user->createToken('Laravel Password Grant Client');
            $response = ["code" => 0, "desc" => "successful", "data" => [
                'acceess_token' => $token->accessToken, 'expires_at' => $token->token['expires_at'], 'user' => $user
            ]];
            $retrieveAuthCode->delete();
            return $response;
        }else{
            return ["code" => 1, "desc" => "unsuccessful"];
        }
    }
}
