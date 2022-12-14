<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Incubator extends Model
{
    use HasFactory;
    protected $hidden = [
        'created_at', 'updated_at'
    ];
    public function allTemperature(){
        return $this->hasOne(Temperature::class, 'incubator_id', 'id');
    }
    public function allHumidity(){
        return $this->hasOne(Humidity::class, 'incubator_id', 'id');
    }
    public function allDioxide(){
        return $this->hasOne(Dioxide::class,'incubator_id', 'id');
    }
}
