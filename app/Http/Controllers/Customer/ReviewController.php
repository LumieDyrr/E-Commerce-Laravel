<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\Review;
use Illuminate\Http\Request;

class ReviewController extends Controller
{
    public function store(Request $request, string $slug)
    {
        $product = Product::where('slug', $slug)->firstOrFail();

        $data = $request->validate([
            'rating' => 'required|integer|between:1,5',
            'comment' => 'nullable|string|max:1000',
        ], [
            'rating.required' => 'Pilih rating terlebih dahulu.',
            'rating.between' => 'Rating harus antara 1 sampai 5.',
            'comment.max' => 'Ulasan maksimal :max karakter.',
        ]);

        // Hanya pembeli yang pesanannya sudah sampai yang boleh memberi ulasan.
        $hasBought = OrderItem::where('product_id', $product->id)
            ->whereHas('order', fn ($q) => $q->where('user_id', auth()->id())->where('status', 'arrived'))
            ->exists();

        abort_unless($hasBought, 403, 'Anda hanya dapat mengulas produk yang pernah Anda terima.');

        Review::updateOrCreate(
            ['user_id' => auth()->id(), 'product_id' => $product->id],
            ['rating' => $data['rating'], 'comment' => $data['comment'] ?? null],
        );

        return redirect()->route('products.show', $product->slug)->with('success', 'Terima kasih, ulasan Anda tersimpan.');
    }
}
