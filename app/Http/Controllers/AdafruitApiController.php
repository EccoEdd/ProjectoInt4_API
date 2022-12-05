<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class AdafruitApiController extends Controller
{
    private string $token = "aio_AlXI66lHxrbSKdmJIm5cv5fN0miO";

    public function ledData(){
        $response = Http::withBasicAuth('X-AIO-Key', $this->token)
            ->get('https://io.adafruit.com/api/v2/Koebeni/feeds/led/data',[

            ]);
        return $response;
    }
}
