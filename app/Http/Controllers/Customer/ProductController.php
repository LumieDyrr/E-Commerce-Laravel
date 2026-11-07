<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    public function index(Request $request)
    {
        $request->validate([
            'q' => 'nullable|string|max:100',
            'kategori' => 'nullable|integer',
            'urut' => 'nullable|in:terbaru,termurah,termahal,nama',
        ]);

        $products = Product::with('category')
            ->search($request->query('q'))
            ->when($request->filled('kategori'), fn ($q) => $q->where('category_id', $request->integer('kategori')))
            ->when(true, function ($q) use ($request) {
                return match ($request->query('urut', 'terbaru')) {
                    'termurah' => $q->orderBy('price'),
                    'termahal' => $q->orderByDesc('price'),
                    'nama' => $q->orderBy('name'),
                    default => $q->latest('id'),
                };
            })
            ->paginate((int) config('shop.per_page', 12))
            ->withQueryString();

        $categories = Category::orderBy('name')->get();

        return view('pages.customers.product.index', compact('products', 'categories'));
    }

    public function show(string $slug)
    {
        $product = Product::with('category')->where('slug', $slug)->firstOrFail();

        $reviews = $product->reviews()->with('user:id,name')->latest()->paginate(5);
        $rating = $product->reviews()->avg('rating');

        $related = Product::where('category_id', $product->category_id)
            ->whereKeyNot($product->id)
            ->where('stock_quantity', '>', 0)
            ->latest('id')->take(4)->get();

        $canReview = auth()->check() && auth()->user()->role === 'customer'
            && \App\Models\OrderItem::where('product_id', $product->id)
                ->whereHas('order', fn ($q) => $q->where('user_id', auth()->id())->where('status', 'arrived'))
                ->exists();

        $myReview = auth()->check() ? $product->reviews()->where('user_id', auth()->id())->first() : null;

        return view('pages.customers.product.show', compact('product', 'reviews', 'rating', 'related', 'canReview', 'myReview'));
    }
}
