<?php

namespace App\Http\Controllers;

use App\Models\Incubator;
use App\Models\Ownership;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use App\Models\Dioxide;
use Illuminate\Support\Facades\Validator;
use PhpParser\Node\Stmt\Do_;

class DioxideController extends Controller
{
    protected string $userA = "JaredLoera";
    protected string $tokenA = "aio_IbYF0720wrGNIrAbHZESAJKVYuYe";

    public function lastDioxideData(Request $request){
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
            ->get('https://io.adafruit.com/api/v2/'.$this->userA.'/feeds/humo/data?limit=1');

        $dioxideOld = Dioxide::query()
            ->where('identifier', '=', $response[0]['id'])
            ->where('incubator_id', '=', $incubator->id)
            ->first();

        if($dioxideOld){
            $dioxide = Dioxide::latest()->where('incubator_id', '=', $incubator->id)->first();
            return response()->json(["Data" => $dioxide]);
        }

        $dioxide = new Dioxide();
        $dioxide->value = $response[0]['value'];
        $dioxide->identifier = $response[0]['id'];
        $dioxide->incubator_id = $incubator->id;
        $dioxide->save();

        return response()->json(["Data" => $dioxide]);
    }

    public function dioxideData(Request $request){
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
        $data = Dioxide::query()->where('incubator_id', '=', $incubator->id)->get();
        $count = Dioxide::query()->where('incubator_id', '=', $incubator->id)->count();

        return response()->json([
            "Count" => $count,
            "Data"  => $data
        ]);
    }

    public function temperatureById(Request $request, int $id){

        $response = Http::withHeaders(['X-AIO-Key' => "llave"])
            ->get('https://io.adafruit.com/api/v2/JaredLoera/feeds/humo/data?limit=1');

        $temperature = new Dioxide();
        $temperature->value = $response[0]['value'];
        $temperature->identifier = $response[0]['id'];
        $temperature->incubator_id = $id;
        $temperature->save();

        return response()->json(["Data" => $temperature]);
    }

}
