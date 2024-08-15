<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class UsersTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table("users")->insert([
           [ 'name' => 'John Doe',
            'email' => 'john@doe.com',
            'password' => Hash::make('1234')],
            ['name'=>'Jane Smith',
            'email'=>'jane@smith.com',
            'password'=>Hash::make('123')]
        ]);
    }
}
