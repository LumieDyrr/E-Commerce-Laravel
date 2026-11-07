<?php

namespace App\Exceptions;

use RuntimeException;

/** Kesalahan bisnis pada alur pesanan (stok habis, status tidak valid, dll). Pesannya aman ditampilkan ke pengguna. */
class OrderException extends RuntimeException
{
}
