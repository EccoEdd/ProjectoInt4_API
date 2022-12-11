<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Role extends Model
{
    use HasFactory;
    protected $hidden = [
        'created_at', 'updated_at', 'id'
    ];
    public function belongsOwner(){
        return $this->belongsTo(Ownership::class, 'id', 'role_id');
    }
}
