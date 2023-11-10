<?php

namespace Database\Seeders;

use App\Models\User;
use DB;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $johnDoe = User::factory()->create([
            'name' => 'John Doe',
            'email' => 'johndoe@example.com',
        ]);

        DB::table('personal_access_tokens')->insert([
            'id' => 1,
            'tokenable_type' => 'App\Models\User',
            'tokenable_id' => $johnDoe->id,
            'name' => 'user',
            'token' => '83d1a1f5e18d9f55c64b63b9211e414221fe5de027a8cd04348441831ccaa373',
            'abilities' => '["*"]',
            'created_at' => '2023-11-11 20:57:59',
            'updated_at' => '2023-11-11 20:57:59'
        ]);

        $janeDoe = User::factory()->create([
            'name' => 'Jane Doe',
            'email' => 'janedoe@example.com',
        ]);

        DB::table('personal_access_tokens')->insert([
            'id' => 2,
            'tokenable_type' => 'App\Models\User',
            'tokenable_id' => $janeDoe->id,
            'name' => 'user',
            'token' => '9d078c320ede98d29c965a37bdd33ef3b7e730205eba073237cc6edd097d035e',
            'abilities' => '["*"]',
            'created_at' => '2023-11-11 21:16:07',
            'updated_at' => '2023-11-11 21:16:07'
        ]);
    }
}
