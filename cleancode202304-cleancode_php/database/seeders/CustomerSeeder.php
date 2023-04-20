<?php

namespace Database\Seeders;

use App\Models\Customer;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class CustomerSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
       $user = User::create([
            'name' => 'Customer1',
            'email' => 'user1@example.com',
            'password' => bcrypt('useruser1'),
            'email_verified_at' => now(),
        ]);
        Customer::create([
            'user_id' => $user->id,
            'first_name' => 'first_name1',
            'last_name' => 'last_name1',
            'status' => 'active',
        ]);
        $user = User::create([
            'name' => 'Customer2',
            'email' => 'user2@example.com',
            'password' => bcrypt('useruser2'),
            'email_verified_at' => now()
        ]);
        Customer::create([
            'user_id' => $user->id,
            'first_name' => 'first_name2',
            'last_name' => 'last_name2',
            'status' => 'active',
        ]);
    }
}
