<?php

namespace App\Http\Controllers;

use App\Models\Incubator;
use App\Models\Ownership;
use App\Models\Temperature;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Validator;

class TemperatureController extends Controller
{
    public function lastTemperatureData(Request $request){
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
            ->get('https://io.adafruit.com/api/v2/'.env('USER_ADAJ').'/feeds/sendtemp/data?limit=1');

        $temperatureOld = Temperature::query()->where('identifier', '=', $response[0]['id'])->first();

        if($temperatureOld){
            $temperature = Temperature::latest()->first();
            return response()->json(["Data" => $temperature]);
        }
        $incubator = Incubator::query()->where('code', '=', $request->code)->first();
        $temperature = new Temperature();
        $temperature->value = $response[0]['value'];
        $temperature->identifier = $response[0]['id'];
        $temperature->incubator_id = $incubator->id;
        $temperature->save();
        return response()->json(["Data" => $temperature]);
    }
    public function temperatureData(Request $request){
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
        $data = Temperature::query()->where('incubator_id', '=', $incubator->id)->get();
        $count = Temperature::query()->where('incubator_id', '=', $incubator->id)->count();
        return response()->json([
            "Count" => $count,
            "Data"  => $data
        ]);
    }
}
