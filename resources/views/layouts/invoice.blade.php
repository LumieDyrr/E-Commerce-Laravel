<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Faktur Pesanan #{{ $order->id }}</title>
    <style>
        body {
            font-family: 'Arial', sans-serif;
            margin: 0;
            padding: 0;
            background-color: #ffffff; /* Warna latar belakang putih */
        }
        .invoice {
            max-width: 800px;
            margin: 30px auto;
            background: #ffffff; /* Warna putih */
            padding: 20px 30px;
            border-radius: 8px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            border: 1px solid #ddd; /* Tambahkan border untuk pembatas */
        }
        .header {
            text-align: center;
            border-bottom: 2px solid #4CAF50;
            padding-bottom: 10px;
            margin-bottom: 20px;
        }
        .header h1 {
            font-size: 28px;
            margin: 0;
            color: #333;
        }
        .header p {
            margin: 0;
            font-size: 14px;
            color: #555;
        }
        .details {
            font-size: 14px;
            color: #555;
        }
        .details p {
            margin: 5px 0;
        }
        .items table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        .items table th, .items table td {
            border: 1px solid #ddd;
            padding: 10px;
            text-align: left;
        }
        .items table th {
            background-color: #4CAF50;
            color: white;
            font-size: 14px;
        }
        .items table td {
            font-size: 14px;
            color: #555;
        }
        .total {
            text-align: right;
            margin-top: 20px;
            font-size: 18px;
            font-weight: bold;
            color: #333;
        }
        .footer {
            text-align: center;
            margin-top: 30px;
            font-size: 12px;
            color: #777;
        }
        .footer a {
            color: #4CAF50;
            text-decoration: none;
        }
    </style>
</head>
<body>
    <div class="invoice">
        <div class="header">
            <h1>Faktur Pesanan</h1>
            <p>#{{ $order->id }}</p>
        </div>
        <div class="details">
            <p><strong>Tanggal Pesanan:</strong> {{ $order->created_at->format('d M Y') }}</p>
            <p><strong>Total:</strong> Rp{{ number_format($order->total_price, 0, ',', '.') }}</p>
            @php
                $statusText = [
                    'pending' => 'Pembayaran Sedang Diperiksa',
                    'delivered' => 'Pesanan Telah Diterima Kurir',
                    'arrived' => 'Pesanan Telah Sampai',
                    'completed' => 'Langganan Anda Telah Disetujui',
                    'rejected' => 'Pesanan Telah Ditolak',
                ];
            @endphp
            <p><strong>Status:</strong> {{ $statusText[$order->status] ?? 'Status Tidak Diketahui' }}</p>
        </div>
        <div class="items">
            <h3>Detail Produk</h3>
            <table>
                <thead>
                    <tr>
                        <th>Nama Produk</th>
                        <th>Jumlah</th>
                        <th>Harga</th>
                        <th>Total</th>
                    </tr>
                </thead>
                <tbody>
                    @if ($order->isSubscription())
                        <tr>
                            <td>Langganan Paket {{ $order->plan_label }}</td>
                            <td>1</td>
                            <td>Rp{{ number_format($order->total_price, 0, ',', '.') }}</td>
                            <td>Rp{{ number_format($order->total_price, 0, ',', '.') }}</td>
                        </tr>
                    @endif
                    @foreach($order->items as $item)
                        <tr>
                            <td>{{ $item->product?->name ?? 'Produk sudah dihapus' }}</td>
                            <td>{{ $item->quantity }}</td>
                            <td>Rp{{ number_format($item->price, 0, ',', '.') }}</td>
                            <td>Rp{{ number_format($item->price * $item->quantity, 0, ',', '.') }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <div class="total">
            Total: Rp{{ number_format($order->total_price, 0, ',', '.') }}
        </div>
        <div class="footer">
            @if (in_array($order->status, ['arrived', 'completed']))
                <p>Terima kasih telah berbelanja dengan kami!</p>
                <p>Kami berharap Anda puas dengan layanan kami. Jika ada pertanyaan, jangan ragu untuk menghubungi kami di:</p>
                <p><a href="mailto:support@company.com">support@company.com</a></p>
            @elseif ($order->status === 'rejected')
                <p>Mohon maaf, pesanan Anda tidak dapat kami proses.</p>
                <p>Untuk informasi lebih lanjut atau bantuan, silakan hubungi kami di:</p>
                <p><a href="mailto:support@company.com">support@company.com</a></p>
            @else
                <p>Terima kasih telah berbelanja dengan kami!</p>
                <p>Hubungi kami: <a href="mailto:support@company.com">Copyright @2024. All rights reserved.</a></p>
            @endif
        </div>
        
    </div>
</body>
</html>
