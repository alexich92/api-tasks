<?php

use Illuminate\Database\Seeder;
use App\Role;

/**
 * Class RolesSeeder
 */
class RolesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run()
    {
        Role::create(['name' => 'User']);
        Role::create(['name' => 'Admin']);
    }
}

