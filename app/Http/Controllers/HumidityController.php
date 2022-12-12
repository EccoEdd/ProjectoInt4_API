<?php

namespace App\Http\Controllers;

use App\Models\Humidity;
use App\Models\Incubator;
use App\Models\Ownership;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rules\In;

class HumidityController extends Controller
{
    public function lastHumidityData(Request $request){
        $validate = Validator::make($request->all(),[
            'code' => 'required|size:5',
        ],[
            'code' => [
                'required' => 'You need the code of your incubator',
                'size'     => 'This code is only 5 characters long'
            ]
        ]);
        if ($validate->fails())
            return response()->json(['Message' => $validate->errors()], 403);
        $ownership = Ownership::query()
            ->where('user_id', '=', $request->user()->id)
            ->first();
        if(!$ownership)
            return response()->json(["Message" => 'Un authorized']);
        $response = Http::withHeaders(['X-AIO-Key' => env('TOKEN_ADAJ')])
            ->get('https://io.adafruit.com/api/v2/'.env('USER_ADAJ').'/feeds/sendhum/data?limit=1');
        $humidityOld = Humidity::query()->where('identifier', '=', $response[0]['id'])->first();

        if($humidityOld){
            $humidity = Humidity::latest()->first();
            return response()->json(["Data" => $humidity]);
        }
        $incubator = Incubator::query()->where('code', '=', $request->code)->first();
        $humidity = new Humidity();
        $humidity->value = $response[0]['value'];
        $humidity->identifier = $response[0]['id'];
        $humidity->incubator_id = $incubator->id;
        $humidity->save();
        return response()->json(["Data" => $humidity]);
    }
    public function humidityData(Request $request){
        $validate = Validator::make($request->all(),[
            'code' => 'required|size:5',
        ],[
            'code' => [
                'required' => 'You need the code of your incubator',
                'size'     => 'This code is only 5 characters long'
            ]
        ]);
        if ($validate->fails())
            return response()->json(['Message' => $validate->errors()], 403);
        $ownership = Ownership::query()
            ->where('user_id', '=', $request->user()->id)
            ->first();
        if(!$ownership)
            return response()->json(["Message" => 'Un authorized']);
        $incubator = Incubator::query()->where('code', '=', $request->code)->first();
        $data = Humidity::query()->where('incubator_id', '=', $incubator->id)->get();
        $count = Humidity::query()->where('incubator_id', '=', $incubator->id)->count();
        return response()->json([
            "Count" => $count,
            "Data"  => $data
        ]);
    }
}
