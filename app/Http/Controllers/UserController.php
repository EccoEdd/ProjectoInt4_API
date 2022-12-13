<?php

namespace App\Http\Controllers;

use App\Jobs\MailSenderAuth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\URL;

class UserController extends Controller
{
    public function newUser(Request $request){
        Log::info("Hola Mundo");
        $validate = Validator::make($request->all(),[
            'name'  => 'required',
            'email' => 'required|unique:users|email:rfc,dns',
            'password' => 'required|min:6'
        ],[
            'name' => [
                'required' => 'You need a username',
            ],
            'email' => [
                'required' => 'You need an email account',
                'unique'   => 'This email has already been taken',
                'email'    => 'This must to be a valid email'
            ],
            'password' => [
                'required' => 'You need to set a password',
                'min'      => 'You need at least 6 characters long'
            ]
        ]);
        if ($validate->fails())
            return response()->json(['Message' => $validate->errors()], 403);

        $user = new User();
        $user->name = $request->name;
        $user->email = $request->email;
        $user->password = Hash::make($request->password);
        $user->save();
        $url = URL::temporarySignedRoute('verify', now()->addMinutes(15), ['id' => $user->id]);
        MailSenderAuth::dispatch($user, $url)->delay(15);

        return response()->json(['Message' =>'You only have 14 minutes left to use the link at your email'], 201);
    }

    public function verifyUser(Request $request, int $id){
        if (!$request->hasValidSignature()){
            abort(401);
        }
        $user = User::find($id);

        if(!$user)
            abort(401);

        if($user->status)
            return response()->json(['Message' => 'Already verified'],202);

        $user->status = true;
        $user->save();
        return response()->json(['Message' =>'Thanks for you time'], 201);
    }

    public function logIn(Request $request){
        $validate = Validator::make($request->all(),[
            'email' => 'required|exists:users',
            'password' => 'required'
        ],[
            'email' => [
                'required' => 'You need an email account',
                'exists'    => 'This user doesn\'t exists'
            ],
            'password' => [
                'required' => 'You need to set a password',
            ]
        ]);
        if ($validate->fails())
            return response()->json(['Message' => $validate->errors()], 403);

        $user = User::where("email", $request->email)->where("status", true)->first();

        if (!$user||!Hash::check($request->password, $user->password))
            return response()->json(["Message" => "Incorrect Data"], 401);

        $token = $user->createToken('auth_token')->plainTextToken;
        return response()->json([
            'Message' => 'Welcome',
            'token'   => $token
        ], 201);
    }

    public function logOut(Request $request){
        $request->user()->tokens()->delete();
        return response()->json([
            'Message' => 'See you next time'
        ]);
    }

    public function userInformation(Request $request){
        $user = User::find($request->user()->id);
        return response()->json([
           'Data' => $user
        ]);
    }

    public function deleteUserData(int $id){
        $user = User::find($id);
        if(!$user)
            return response()->json(['Message' => 'User Doesn\'t Exists']);
        $user->delete();
        return response()->json([
            'Message' => 'Data deleted',
            'Data'    => $user
        ]);
    }

    public function allUsers(){
        $data = User::all();
        $count = User::all()->count();
        return response()->json([
            "Count" => $count,
            "Data" =>  $data
        ]);
    }


}
