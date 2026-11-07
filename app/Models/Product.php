<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Storage;

class Product extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'name',
        'description',
        'price',
        'category_id',
        'stock_quantity',
        'discount',
        'image',
        'slug',
    ];

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function reviews()
    {
        return $this->hasMany(Review::class);
    }

    /** Harga setelah diskon produk (belum termasuk diskon member). */
    public function getFinalPriceAttribute(): float
    {
        return round($this->price * (1 - min(max($this->discount, 0), 100) / 100), 2);
    }

    public function getInStockAttribute(): bool
    {
        return $this->stock_quantity > 0;
    }

    public function getImageUrlAttribute(): string
    {
        if ($this->image && Storage::disk('public')->exists('products/' . $this->image)) {
            return asset('storage/products/' . $this->image);
        }

        // Placeholder SVG inline, tidak butuh file tambahan.
        $svg = '<svg xmlns="http://www.w3.org/2000/svg" width="400" height="400"><rect width="100%" height="100%" fill="#e2e8f0"/>'
            . '<text x="50%" y="50%" fill="#94a3b8" font-family="Arial" font-size="20" text-anchor="middle" dy=".3em">Tanpa Gambar</text></svg>';

        return 'data:image/svg+xml;base64,' . base64_encode($svg);
    }

    public function scopeSearch(Builder $query, ?string $term): Builder
    {
        $term = trim((string) $term);

        return $term === '' ? $query : $query->where(function ($q) use ($term) {
            $like = '%' . str_replace(['%', '_'], ['\%', '\_'], $term) . '%';
            $q->where('name', 'like', $like)->orWhere('description', 'like', $like);
        });
    }
}
