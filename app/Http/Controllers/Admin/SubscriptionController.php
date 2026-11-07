<?php

namespace App\Http\Controllers\Admin;

use App\Exceptions\OrderException;
use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Services\OrderService;

class SubscriptionController extends Controller
{
    public function index()
    {
        $pendingOrders = Order::subscriptions()->where('status', 'pending')
            ->with(['user', 'payment'])->latest()->paginate(10);

        return view('pages.admin.subscription.index', compact('pendingOrders'));
    }

    public function approve(string $id, OrderService $orders)
    {
        $order = Order::subscriptions()->findOrFail($id);

        try {
            $orders->approveSubscription($order);
        } catch (OrderException $e) {
            return redirect()->route('dashboard.subscriptions.index')->with('error', $e->getMessage());
        }

        return redirect()->route('dashboard.subscriptions.index')->with('success', 'Langganan pengguna berhasil disetujui.');
    }

    public function reject(string $id, OrderService $orders)
    {
        $order = Order::subscriptions()->findOrFail($id);

        try {
            $orders->reject($order);
        } catch (OrderException $e) {
            return redirect()->route('dashboard.subscriptions.index')->with('error', $e->getMessage());
        }

        return redirect()->route('dashboard.subscriptions.index')->with('success', 'Langganan pengguna berhasil ditolak.');
    }
}
