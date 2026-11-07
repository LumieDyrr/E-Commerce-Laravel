<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class AdminSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('users')->insert([
            ['name' => 'Admin', 'email' => 'admin@admin.com', 'password' => bcrypt('admin123'), 'phone' => '081234567890' , 'role' => 'admin', 'created_at' => now(), 'updated_at' => now()],
        ]);
    }
}
