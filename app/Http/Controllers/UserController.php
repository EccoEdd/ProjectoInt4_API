<?php

namespace App\Http\Controllers;

//use App\Jobs\MailSenderAuth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\URL;

class UserController extends Controller
{
    public function newUser(Request $request){
        $validate = Validator::make($request->all(),[
            'name'  => 'required',
            'email' => 'required|email:rfc,dns',
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
            return response()->json(['errors' => $validate->errors()], 403);

        $user = new User();
        $user->name = $request->name;
        $user->email = $request->email;
        $user->password = Hash::make($request->password);
        $user->save();

        $url = URL::temporarySignedRoute('verify', now()->addMinutes(60), ['id' => $user->id]);
        //MailSenderAuth::dispatch($user, $url)->delay(1);

        return response()->json(['Message' =>'Success...'], 201);
    }
    public function verifyUser(Request $request){
        if (!$request->hasValidSignature()) {
            abort(401);
        }
        $validate = Validator::make($request->all(), [
            'id' => 'required|exists:users'
        ],[
            'id' => [
                'required' => 'It needs the id',
                'exists'   => 'This id doesn\'t exists'
            ]
        ]);
        if ($validate->fails())
            return response()->json(['errors' => $validate->errors()], 403);
        $user = User::find($request->id);
        $user->status = true;
        $user->save();
        return response()->json(['Message' =>'Success...'], 201);
    }
    public function logIn(Request $request){
        $validate = Validator::make($request->all(),[
            'email' => 'required|email:rfc,dns',
            'password' => 'required|min:6'
        ],[
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
            return response()->json(['errors' => $validate->errors()], 403);
    }
    public function logOut(Request $request){
        $request->user()->tokens()->delete();
        return response()->json([

        ]);
    }
}
