<?php

return [
    // Jumlah produk per halaman di katalog
    'per_page' => 12,

    // Stok di bawah/sama dengan angka ini dianggap "menipis" (dashboard admin)
    'low_stock_threshold' => 5,

    // Lama langganan (hari)
    'subscription_days' => 30,

    // Paket langganan: harga (Rp) dan diskon member (%)
    'plans' => [
        'basic'     => ['label' => 'Basic',     'price' => 199000, 'discount' => 5],
        'premium'   => ['label' => 'Premium',   'price' => 299000, 'discount' => 15],
        'eksklusif' => ['label' => 'Eksklusif', 'price' => 499000, 'discount' => 20],
    ],
];
