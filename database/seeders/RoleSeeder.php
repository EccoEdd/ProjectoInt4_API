<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Role;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
       $role1 = new Role();
       $role2 = new Role();

       $role1->role = 'o';
       $role2->role = 'v';
       $role1->description = "Owner of Incubator";
       $role2->description = "Visitor of data";

       $role1->save();
       $role2->save();
    }
}
