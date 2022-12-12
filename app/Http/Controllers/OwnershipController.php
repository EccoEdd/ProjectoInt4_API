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
        $data = Ownership::query()->where('user_id', '=', $request->user()->id)->get();
        Log::info($data);
        if(!$data)
            return response()->json(["Message" => "No access"]);
        return response()->json(["Data" => $data]);
    }
}
