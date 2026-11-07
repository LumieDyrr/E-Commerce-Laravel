<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Cart;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ProductController extends Controller
{
    public function index()
    {
        $products = Product::with('category')->latest('id')->paginate(10);

        return view('pages.admin.product.index', compact('products'));
    }

    public function create()
    {
        return view('pages.admin.product.create');
    }

    public function store(Request $request)
    {
        $validatedData = $request->validate($this->rules(), $this->messages());

        if ($request->hasFile('image')) {
            $validatedData['image'] = $this->storeImage($request);
        }

        $validatedData['discount'] = $validatedData['discount'] ?? 0;
        $validatedData['slug'] = $this->uniqueSlug($validatedData['name']);

        Product::create($validatedData);

        return redirect()->route('dashboard.products.index')->with('success', 'Produk berhasil ditambahkan.');
    }

    public function edit($slug)
    {
        $product = Product::where('slug', $slug)->firstOrFail();

        return view('pages.admin.product.edit', compact('product'));
    }

    public function update(Request $request, $slug)
    {
        $product = Product::where('slug', $slug)->firstOrFail();

        $validatedData = $request->validate($this->rules(), $this->messages());
        $validatedData['discount'] = $validatedData['discount'] ?? 0;

        if ($request->hasFile('image')) {
            $this->deleteImage($product->image);
            $validatedData['image'] = $this->storeImage($request);
        } else {
            unset($validatedData['image']);
        }

        // Slug sengaja tidak diubah agar tautan produk tetap stabil.
        $product->update($validatedData);

        return redirect()->route('dashboard.products.index')->with('success', 'Produk berhasil diperbarui.');
    }

    public function destroy($id)
    {
        $product = Product::findOrFail($id);

        // Soft delete: riwayat pesanan lama tetap utuh. Gambar dipertahankan untuk riwayat.
        Cart::where('product_id', $product->id)->delete();
        $product->delete();

        return redirect()->route('dashboard.products.index')->with('success', 'Produk berhasil dihapus.');
    }

    private function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'description' => 'required|string',
            'price' => 'required|numeric|min:0|max:99999999',
            'category_id' => 'required|exists:categories,id',
            'stock_quantity' => 'required|integer|min:0|max:1000000',
            'discount' => 'nullable|numeric|between:0,100',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:10240',
        ];
    }

    private function messages(): array
    {
        return [
            'name.required' => 'Nama produk harus diisi.',
            'name.string' => 'Nama produk harus berupa teks.',
            'name.max' => 'Nama produk tidak boleh lebih dari :max karakter.',
            'description.required' => 'Deskripsi produk harus diisi.',
            'description.string' => 'Deskripsi produk harus berupa teks.',
            'price.required' => 'Harga produk harus diisi.',
            'price.numeric' => 'Harga produk harus berupa angka.',
            'price.min' => 'Harga produk tidak boleh negatif.',
            'price.max' => 'Harga produk terlalu besar.',
            'category_id.required' => 'Kategori produk harus dipilih.',
            'category_id.exists' => 'Kategori produk tidak ditemukan.',
            'stock_quantity.required' => 'Stok produk harus diisi.',
            'stock_quantity.integer' => 'Stok produk harus berupa angka.',
            'stock_quantity.min' => 'Stok produk tidak boleh negatif.',
            'discount.numeric' => 'Diskon produk harus berupa angka.',
            'discount.between' => 'Diskon produk harus antara 0 sampai 100 persen.',
            'image.image' => 'File yang diunggah harus berupa gambar.',
            'image.mimes' => 'Format file gambar harus jpeg, png, jpg, gif, atau webp.',
            'image.max' => 'Ukuran file gambar tidak boleh lebih dari 10 MB.',
        ];
    }

    /** Simpan gambar dengan nama acak agar tidak saling menimpa; mengembalikan nama file saja. */
    private function storeImage(Request $request): string
    {
        return basename($request->file('image')->store('products', 'public'));
    }

    private function deleteImage(?string $image): void
    {
        if ($image) {
            Storage::disk('public')->delete('products/' . $image);
        }
    }

    /** Slug unik (termasuk produk yang sudah di-soft-delete karena kolom slug bersifat unik). */
    private function uniqueSlug(string $name): string
    {
        $base = Str::slug($name) ?: 'produk';
        $slug = $base;
        $i = 2;

        while (Product::withTrashed()->where('slug', $slug)->exists()) {
            $slug = $base . '-' . $i++;
        }

        return $slug;
    }
}
