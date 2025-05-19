<!DOCTYPE html>
<html>
<head>
    <title>Receipt</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            max-width: 400px;
            margin: auto;
        }
        .center {
            text-align: center;
        }
        .bold {
            font-weight: bold;
        }
        .item, .totals, .payment {
            display: flex;
            justify-content: space-between;
        }
        .totals .bold, .payment .bold {
            font-weight: bold;
        }
        .footer {
            margin-top: 20px;
            text-align: center;
        }
        hr {
            border: none;
            border-top: 1px solid #ccc;
        }
    </style>
</head>
<body>

    <div class="center">
        <h2>DumpStreet</h2>
        <p>Your business in the cloud<br><small>an application by <b>WallaceIT</b></small></p>
    </div>

    <p><b>Transaction Ref:</b> {{ $mainSale->sales_id }}</p>
    <p><b>Sale Time:</b> {{ $mainSale->sales_date }}</p>

    <hr>

    @foreach($sales as $sales)
        <div class="item">
            <div>{{ $sales->sales_quantity }} x {{ $sales->product_name }} (₱{{ number_format($sales->sale_price_per_unit, 2) }})</div>
            <div>₱{{ number_format($sales->amount, 2) }}</div>
        </div>
    @endforeach

    <hr>

    <div class="totals">
        <div>Subtotal:</div>
        <div class="bold">₱{{ number_format($mainSale->subtotal, 2) }}</div>
    </div>
    <div class="totals">
        <div>Tax:</div>
        <div>₱{{ number_format($mainSale->tax, 2) }}</div>
    </div>
    <div class="totals">
        <div><b>Total ({{ $mainSale->items }} items):</b></div>
        <div class="bold">${{ number_format($mainSale->total_amount, 2) }}</div>
    </div>

    <br>

    <div class="payment">
        <div>Payment Method:</div>
        <div>{{ $mainSale->payment_method}}</div>
    </div>

    <div class="footer">
        <p>Thanks for shopping with us!</p>
    </div>

</body>
</html>
