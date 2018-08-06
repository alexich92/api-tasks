<?php

use Illuminate\Database\Seeder;
use App\User;
use App\Role;

/**
 * Class UsersSeeder
 */
class UsersSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run()
    {
        User::create([
            'email' => 'alexich_92@yahoo.com',
            'name' => 'Alex',
            'status' => User::ACCOUNT_ACTIVATED,
            'password' => app('hash')->make('123456'),
            'role_id' => Role::ADMIN_USER
        ]);
    }
}
