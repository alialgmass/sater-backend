<!DOCTYPE html>
<html>
<head>
    <title>Invoice</title>
    <style>
        body { font-family: sans-serif; }
        .container { width: 100%; margin: 0 auto; }
        .header { text-align: center; margin-bottom: 20px; }
        .content { margin-top: 20px; }
        table { width: 100%; border-collapse: collapse; }
        th, td { border: 1px solid #ddd; padding: 8px; }
        th { background-color: #f2f2f2; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>Invoice</h1>
        </div>
        <div class="content">
            <p><strong>Order Number:</strong> {{ $order->order_number }}</p>
            <p><strong>Order Date:</strong> {{ $order->created_at->toFormattedDateString() }}</p>
            <hr>
            <h2>Order Details</h2>
            <table>
                <thead>
                    <tr>
                        <th>Product</th>
                        <th>Quantity</th>
                        <th>Price</th>
                        <th>Total</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($order->items as $item)
                        <tr>
                            <td>{{ $item->product_name }}</td>
                            <td>{{ $item->quantity }}</td>
                            <td>{{ $item->price }}</td>
                            <td>{{ $item->price * $item->quantity }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
            <hr>
            <h3>Total: {{ $order->total_amount }}</h3>
        </div>
    </div>
</body>
</html>
