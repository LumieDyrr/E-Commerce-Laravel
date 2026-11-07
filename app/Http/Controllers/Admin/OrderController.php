<?php

namespace App\Http\Controllers\Admin;

use App\Exceptions\OrderException;
use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Services\OrderService;
use Illuminate\Http\Request;

class OrderController extends Controller
{
    public function index()
    {
        $pendingOrders = Order::products()->where('status', 'pending')
            ->with(['user', 'items', 'payment'])->latest()->paginate(10, ['*'], 'pending_page');

        $orders = Order::products()->where('status', '!=', 'pending')
            ->with(['user', 'items', 'payment'])->latest()->paginate(10, ['*'], 'orders_page');

        return view('pages.admin.order.index', compact('pendingOrders', 'orders'));
    }

    public function manage($id)
    {
        $order = Order::products()->with(['user', 'items.product', 'payment'])->findOrFail($id);

        return view('pages.admin.order.manage', compact('order'));
    }

    public function send(Request $request, $id, OrderService $orders)
    {
        $validatedData = $request->validate([
            'tracking_number' => 'required|string|max:100',
        ], [
            'tracking_number.required' => 'Nomor Resi harus diisi.',
            'tracking_number.max' => 'Nomor Resi maksimal :max karakter.',
        ]);

        $order = Order::products()->findOrFail($id);

        try {
            $orders->ship($order, $validatedData['tracking_number']);
        } catch (OrderException $e) {
            return redirect()->route('dashboard.orders.manage', $order->id)->with('error', $e->getMessage());
        }

        return redirect()->route('dashboard.orders.index')->with('success', 'Order berhasil dikirim.');
    }

    public function reject($id, OrderService $orders)
    {
        $order = Order::products()->findOrFail($id);

        try {
            $orders->reject($order);
        } catch (OrderException $e) {
            return redirect()->route('dashboard.orders.index')->with('error', $e->getMessage());
        }

        return redirect()->route('dashboard.orders.index')->with('success', 'Order berhasil ditolak.');
    }
}
