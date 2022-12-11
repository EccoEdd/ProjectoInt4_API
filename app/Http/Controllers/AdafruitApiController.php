<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class AdafruitApiController extends Controller
{
    protected string $user = "Kobeni";
    protected string $token = "aio_TxPA64SDsnIMtMXJ4AhLy0ha8eLI";

    public function ledData(){
        $response = Http::withHeaders(['X-AIO-Key' => env('TOKEN_ADAJ')])
            ->get('https://io.adafruit.com/api/v2/'.env('USER_ADAJ').'/feeds/sendtemp/data?limit=1');
        return $response;
    }
}
