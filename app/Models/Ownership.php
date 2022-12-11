<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Ownership extends Model
{
    use HasFactory;

    protected $hidden = [
      'incubator_id', 'role_id', 'updated_at'
    ];

    public function userData(){
        return $this->hasOne(User::class, 'id', 'user_id');
    }
    public function roleData(){
        return $this->hasOne(Role::class, 'id', 'role_id');
    }
    public function incubatorData(){
        return $this->hasOne(Incubator::class, 'id', 'incubator_id');
    }
}
