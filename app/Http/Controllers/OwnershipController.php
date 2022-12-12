<?php

namespace App\Http\Controllers;

use App\Models\Incubator;
use App\Models\Ownership;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class OwnershipController extends Controller
{
    public function checkOwnership(Request $request){
        $validate = Validator::make($request->all(),[
            'code' => 'required|size:5|exists:incubators',
        ],[
            'code' => [
                'required' => 'You need the code of your incubator',
                'size'     => 'This code is only 5 characters long',
                'exists'   => 'The incubator must exists'
            ]
        ]);
        if ($validate->fails())
            return response()->json(['Message' => $validate->errors()], 403);
        $incubator = Incubator::query()->where('code', '=', $request->code)->first();

        $data = Ownership::query()->where('incubator_id', '=', $incubator->id)
            ->where('user_id', '=', $request->user()->id)->first();

        Log::info($data);

        if(!$data)
            return response()->json(["Message" => "No access"]);

        return response()->json(["Data" => $data]);
    }
}
