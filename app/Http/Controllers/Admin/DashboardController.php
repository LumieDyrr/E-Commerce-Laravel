<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;

class DashboardController extends Controller
{
    public function index()
    {
        $threshold = (int) config('shop.low_stock_threshold', 5);

        $stats = [
            'revenue' => Order::whereIn('status', ['delivered', 'arrived', 'completed'])->sum('total_price'),
            'revenue_month' => Order::whereIn('status', ['delivered', 'arrived', 'completed'])
                ->where('created_at', '>=', now()->startOfMonth())->sum('total_price'),
            'pending_orders' => Order::products()->where('status', 'pending')->count(),
            'pending_subscriptions' => Order::subscriptions()->where('status', 'pending')->count(),
            'customers' => User::where('role', 'customer')->count(),
            'products' => Product::count(),
        ];

        $lowStock = Product::where('stock_quantity', '<=', $threshold)->orderBy('stock_quantity')->take(8)->get();
        $recentOrders = Order::with('user')->latest()->take(6)->get();

        return view('pages.admin.dashboard.index', compact('stats', 'lowStock', 'recentOrders', 'threshold'));
    }
}
