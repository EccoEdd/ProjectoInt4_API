<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Models\Ownership;
use App\Models\Incubator;
use App\Models\User;
use Illuminate\Validation\Rules\In;


class IncubatorController extends Controller
{
    public function addIncubator(Request $request){
        $validate = Validator::make($request->all(),[
            'code' => 'required|unique:incubators|size:5'
        ],[
            'code' => [
                'required' => 'You need the code of your incubator',
                'unique'   => 'This Incubator already has been registered, you can still use it but you will
                                    need to ask for the Owner\'s permission',
                'size'     => 'This code is only 5 characters long'
            ]
        ]);
        if ($validate->fails())
            return response()->json(['Message' => $validate->errors()], 403);

        $incubator = new Incubator();
        $incubator->code = $request->code;
        $incubator->save();

        $ownership = new Ownership();
        $ownership->user_id = $request->user()->id;
        $ownership->incubator_id = $incubator->id;
        $ownership->role_id = 1;
        $ownership->save();

        return response()->json([
           'Message' => 'Success..'
        ], 201);
    }
    public function showAllIncubators(Request $request){
        $ownership = Ownership::query()
            ->where('user_id', '=', $request->user()->id)
                ->with('incubatorData')
                    ->with('roleData')
                        ->get();

        return response()->json([
            'Data' => $ownership
        ]);
    }

    public function addVisitor(Request $request){
        $validate = Validator::make($request->all(),[
            'code' => 'required'
        ],[
            'code' => [
                'required' => 'You need the code of your incubator'
            ]
        ]);
        if ($validate->fails())
            return response()->json(['Message' => $validate->errors()], 403);

    }
}
