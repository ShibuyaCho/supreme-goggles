@php
    $sale = $sale ?? null;
    $storeName = config('services.pos.store_name', 'Cannabest POS');
    $ts = optional($sale->created_at)->timezone(config('app.timezone'))->format('Y-m-d h:ia');
    if (is_array($sale->cart_items)) {
        $items = $sale->cart_items;
    } elseif (is_array($sale->cart)) {
        $items = $sale->cart;
    } else {
        $items = ($sale->saleItems ?? collect())->map(function($it){
            return [
                'name' => $it->product->name ?? $it->product_name ?? 'Item',
                'quantity' => (float)($it->quantity ?? 1),
                'category' => strtolower((string)($it->product_category ?? ($it->product->category ?? ''))),
                'weight' => null,
            ];
        })->values()->all();
    }
@endphp
<!doctype html>
<html>
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Exit Labels #{{ $sale->sale_number ?? $sale->id }}</title>
  <style>
    body{font-family:Arial, Helvetica, sans-serif; margin:0; padding:12px;}
    .label{width:300px; border:1px solid #e5e7eb; border-radius:6px; padding:10px; margin:8px auto;}
    .hdr{font-weight:700; font-size:14px; text-align:center;}
    .row{display:flex; justify-content:space-between; font-size:12px; margin:2px 0;}
    .muted{color:#6b7280; font-size:11px}
    .grid{display:grid; grid-template-columns:repeat(auto-fill, minmax(300px, 1fr)); gap:8px}
    @media print { .grid { grid-template-columns: repeat(2, 1fr); gap: 6px } }
  </style>
</head>
<body>
  <div class="grid">
    @foreach($items as $i)
      @php
        $name = $i['name'] ?? 'Item';
        $qty = (float)($i['quantity'] ?? 1);
        $cat = strtolower((string)($i['category'] ?? $i['product_category'] ?? ''));
        $w = (string)($i['weight'] ?? $i['selectedWeight'] ?? '');
        $isFlower = $cat === 'flower' || preg_match('/\b(g|gram|grams)\b/i', $w);
        $qtyDisp = $isFlower ? number_format($qty, 2) . ' g' : number_format($qty) . ' units';
      @endphp
      <div class="label">
        <div class="hdr">{{ $storeName }}</div>
        <div class="row"><span>Product</span><span>{{ $name }}</span></div>
        <div class="row"><span>{{ $isFlower ? 'Weight' : 'Quantity' }}</span><span>{{ $qtyDisp }}</span></div>
        <div class="row"><span>Sale #</span><span>{{ $sale->sale_number ?? $sale->id }}</span></div>
        <div class="row"><span>Date</span><span>{{ $ts }}</span></div>
        <div class="muted">Thank you for shopping at {{ $storeName }}.</div>
      </div>
    @endforeach
  </div>
  <script>
    window.onload = function(){ try { if (new URLSearchParams(location.search).get('reprint')) window.print(); } catch(e){} };
  </script>
</body>
</html>
