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
    protected string $userA = "JaredLoera";
    protected string $tokenA = "aio_ohKO79LU7T0p1Baes8KhT9F0KnWV";

    public function lastTemperatureData(Request $request){
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

        $ownership = Ownership::query()
            ->where('user_id', '=', $request->user()->id)
            ->where('incubator_id', '=', $incubator->id)
            ->first();

        if(!$ownership)
            return response()->json(["Message" => 'Unauthorized']);

        $response = Http::withHeaders(['X-AIO-Key' => $this->tokenA])
            ->get('https://io.adafruit.com/api/v2/'.$this->userA.'/feeds/sendtemp/data?limit=1');

        $temperatureOld = Temperature::query()
            ->where('identifier', '=', $response[0]['id'])
            ->where('incubator_id', '=', $incubator->id)
            ->first();

        if($temperatureOld){
            $temperature = Temperature::latest()->where('incubator_id', '=', $incubator->id)->first();
            return response()->json(["Data" => $temperature]);
        }

        $temperature = new Temperature();
        $temperature->value = $response[0]['value'];
        $temperature->identifier = $response[0]['id'];
        $temperature->incubator_id = $incubator->id;
        $temperature->save();

        return response()->json(["Data" => $temperature]);
    }
    public function temperatureData(Request $request){
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

        $ownership = Ownership::query()
            ->where('user_id', '=', $request->user()->id)
            ->where('incubator_id', '=', $incubator->id)
            ->first();

        if(!$ownership)
            return response()->json(["Message" => 'Unauthorized']);

        $data = Temperature::query()->where('incubator_id', '=', $incubator->id)->get();
        $count = Temperature::query()->where('incubator_id', '=', $incubator->id)->count();

        return response()->json([
            "Count" => $count,
            "Data"  => $data
        ]);
    }

    public function lastData(){
        $response = Http::withHeaders(['X-AIO-Key' => $this->tokenA])
            ->get('https://io.adafruit.com/api/v2/'.$this->userA.'/feeds/sendtemp/data?limit=1');
        return $response;
    }
}
