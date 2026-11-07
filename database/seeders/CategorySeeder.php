<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class CategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('categories')->insert([
            ['name' => 'Pakaian Pria', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Pakaian Wanita', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Pakaian Anak', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Sepatu', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Aksesoris', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Perlengkapan Olahraga', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Tas', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Pakaian Dalam', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Pakaian Musim Dingin', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Pakaian Tidur', 'created_at' => now(), 'updated_at' => now()],
        ]);
    }
}
