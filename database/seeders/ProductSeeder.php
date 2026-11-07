<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ProductSeeder extends Seeder
{
    public function run()
    {
        DB::table('products')->insert([
            [
                'name' => "Men's Polo Shirt",
                'description' => 'Kaos Polo Lengan Pendek',
                'price' => 150000,
                'category_id' => 1,
                'stock_quantity' => 100,
                'discount' => 0,
                'image' => 'mens-polo-shirt-short-sleeve.jpg',
                'slug' => 'mens-polo-shirt-short-sleeve',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => "Men's Jeans",
                'description' => 'Celana Jeans Biru',
                'price' => 250000,
                'category_id' => 1, // Pakaian Pria
                'stock_quantity' => 80,
                'discount' => 0,
                'image' => 'mens-jeans-blue-denim.jpg',
                'slug' => 'mens-jeans-blue-denim',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => "Men's Formal Shirt",
                'description' => 'Kemeja Lengan Panjang',
                'price' => 200000,
                'category_id' => 1, // Pakaian Pria
                'stock_quantity' => 50,
                'discount' => 0,
                'image' => 'mens-formal-shirt-long-sleeve.jpg',
                'slug' => 'mens-formal-shirt-long-sleeve',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => "Men's Leather Jacket",
                'description' => 'Jaket Kulit Hitam',
                'price' => 500000,
                'category_id' => 1, // Pakaian Pria
                'stock_quantity' => 30,
                'discount' => 0,
                'image' => 'mens-leather-jacket-black.jpg',
                'slug' => 'mens-leather-jacket-black',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => "Men's Hoodie",
                'description' => 'Hoodie Lengan Panjang',
                'price' => 180000,
                'category_id' => 1, // Pakaian Pria
                'stock_quantity' => 60,
                'discount' => 0,
                'image' => 'mens-hoodie-long-sleeve.jpg',
                'slug' => 'mens-hoodie-long-sleeve',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            // [
            //     'name' => "Women's T-Shirt",
            //     'description' => 'T-Shirt Casual',
            //     'price' => 120000,
            //     'category_id' => 2, // Pakaian Wanita
            //     'stock_quantity' => 100,
            //     'discount' => 0,
            //     'image' => 'womens-tshirt-casual.jpg',
            //     'slug' => 'womens-tshirt-casual',
            //     'created_at' => now(),
            //     'updated_at' => now(),
            // ],
            // [
            //     'name' => "Women's Mini Skirt",
            //     'description' => 'Rok Mini Untuk Pesta',
            //     'price' => 170000,
            //     'category_id' => 2, // Pakaian Wanita
            //     'stock_quantity' => 50,
            //     'discount' => 0,
            //     'image' => 'womens-mini-skirt-party.jpg',
            //     'slug' => 'womens-mini-skirt-party',
            //     'created_at' => now(),
            //     'updated_at' => now(),
            // ],
            // [
            //     'name' => "Women's Dress",
            //     'description' => 'Dress Panjang Formal',
            //     'price' => 250000,
            //     'category_id' => 2, // Pakaian Wanita
            //     'stock_quantity' => 40,
            //     'discount' => 10, // Diskon untuk satu produk
            //     'image' => 'womens-dress-long-formal.jpg',
            //     'slug' => 'womens-dress-long-formal',
            //     'created_at' => now(),
            //     'updated_at' => now(),
            // ],
            // [
            //     'name' => "Women's Cardigan",
            //     'description' => 'Cardigan Bahan Rajut',
            //     'price' => 150000,
            //     'category_id' => 2, // Pakaian Wanita
            //     'stock_quantity' => 50,
            //     'discount' => 0,
            //     'image' => 'womens-cardigan-knitted.jpg',
            //     'slug' => 'womens-cardigan-knitted',
            //     'created_at' => now(),
            //     'updated_at' => now(),
            // ],
            // [
            //     'name' => "Kids T-Shirt",
            //     'description' => 'Kaos Bergambar Kartun',
            //     'price' => 90000,
            //     'category_id' => 3, // Pakaian Anak
            //     'stock_quantity' => 100,
            //     'discount' => 0,
            //     'image' => 'kids-tshirt-cartoon-print.jpg',
            //     'slug' => 'kids-tshirt-cartoon-print',
            //     'created_at' => now(),
            //     'updated_at' => now(),
            // ],
            // [
            //     'name' => "Casual Sneakers",
            //     'description' => 'Sepatu Casual Warna Putih Bertali',
            //     'price' => 300000,
            //     'category_id' => 4, // Sepatu
            //     'stock_quantity' => 70,
            //     'discount' => 0,
            //     'image' => 'casual-sneakers-white-lace-up.jpg',
            //     'slug' => 'casual-sneakers-white-lace-up',
            //     'created_at' => now(),
            //     'updated_at' => now(),
            // ],
            // [
            //     'name' => "Bucket Hat",
            //     'description' => 'Topi Bundar Bahan Canvas',
            //     'price' => 100000,
            //     'category_id' => 5, // Aksesoris
            //     'stock_quantity' => 50,
            //     'discount' => 0,
            //     'image' => 'bucket-hat-canvas.jpg',
            //     'slug' => 'bucket-hat-canvas',
            //     'created_at' => now(),
            //     'updated_at' => now(),
            // ],
            // Tambahkan produk lainnya sesuai pola yang sama...
        ]);
    }
}