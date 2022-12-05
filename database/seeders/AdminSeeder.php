<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class AdminSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $user = new User();
        $user->name = "Kobeni";
        $user->email = "kobeni@gmail.com";
        $user->password = Hash::make('kobeni');
        $user->status = true;
        $user->role = 'a';
        $user->save();
    }
}
