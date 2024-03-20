<?php

namespace Database\Seeders;


use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Schema;

class AdminSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Schema::disableForeignKeyConstraints();
        User::truncate();
        Schema::enableForeignKeyConstraints();

            User::insert([
                'name'=> "admin",
                'email'=> "admin@gmail.com",
                'password' => bcrypt('password'),
                'role_id' => 1,
                'verified_by' =>1,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now()
            ]);

    }
}
