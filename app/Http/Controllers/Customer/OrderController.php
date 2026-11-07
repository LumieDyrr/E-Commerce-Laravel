<?php

namespace App\Http\Controllers\Customer;

use App\Exceptions\OrderException;
use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Services\OrderService;
use Barryvdh\DomPDF\Facade\Pdf;

class OrderController extends Controller
{
    public function index()
    {
        $orders = Order::where('user_id', auth()->id())
            ->with(['items.product', 'shippings', 'payment'])
            ->latest()
            ->paginate(10);

        return view('pages.customers.order.index', compact('orders'));
    }

    public function generateInvoice($id)
    {
        $order = Order::where('user_id', auth()->id())->with('items.product', 'user')->findOrFail($id);

        return Pdf::loadView('layouts.invoice', compact('order'))->stream("invoice-order-{$order->id}.pdf");
    }

    public function received($id, OrderService $orders)
    {
        $order = Order::where('user_id', auth()->id())->findOrFail($id);

        try {
            $orders->markReceived($order);
        } catch (OrderException $e) {
            return redirect()->route('orders.index')->with('error', $e->getMessage());
        }

        return redirect()->route('orders.index')->with('success', 'Order berhasil diterima.');
    }
}
