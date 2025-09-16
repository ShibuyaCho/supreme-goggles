@php
    $sale = $sale ?? null;
    $storeName = config('services.pos.store_name', 'Cannabest POS');
    $storeAddress = config('services.pos.store_address', '');
    $storePhone = data_get($sale, 'meta.store_phone', config('services.pos.store_phone', ''));
    $website = data_get($sale, 'meta.website', config('services.pos.website', ''));
    $register = data_get($sale, 'meta.register') ?? data_get($sale, 'meta.till') ?? data_get($sale, 'meta.drawer');
    $customerType = strtolower((string)($sale->customer_type ?? data_get($sale->customer, 'type', 'recreational')));
    $customerType = $customerType === 'medical' ? 'Medical' : 'Recreational';
    $ts = optional($sale->created_at)->timezone(config('app.timezone'))->format('Y-m-d h:ia');
    $cart = is_array($sale->cart) ? $sale->cart : [];
    $items = collect($cart)->map(function($i){
        $name = $i['name'] ?? 'Item';
        $price = (float)($i['price'] ?? 0);
        $qty = (float)($i['quantity'] ?? 1);
        $category = strtolower((string)($i['category'] ?? $i['product_category'] ?? ''));
        $weightStr = (string)($i['weight'] ?? $i['selectedWeight'] ?? '');
        $isFlower = $category === 'flower' || preg_match('/\b(g|gram|grams)\b/i', $weightStr);
        $unitDisplay = $isFlower ? number_format($qty, 2) . ' g' : number_format($qty) . ' x';
        $discAmt = 0.0;
        if (isset($i['discount']) && is_array($i['discount']) && isset($i['discount']['amount'])) {
            $discAmt = (float)$i['discount']['amount'] * $qty;
        }
        $lineBase = $price * $qty;
        $lineTotal = max(0, $lineBase - $discAmt);
        return compact('name','price','qty','isFlower','unitDisplay','discAmt','lineBase','lineTotal');
    });
    $itemDiscountTotal = (float)$items->sum('discAmt');
    $cartDiscount = (float)($sale->discount_amount ?? 0);
    $subtotal = (float)($sale->subtotal ?? 0);
    $tax = (float)($sale->tax_amount ?? $sale->tax ?? 0);
    $total = (float)($sale->total_amount ?? $sale->total ?? 0);
    $changeDue = (float) (data_get($sale, 'meta.change_due') ?? data_get($sale, 'meta.change') ?? 0);
    $footer = config('services.pos.receipt_footer', "Thank you for shopping at {$storeName}");
@endphp
<!doctype html>
<html>
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Receipt #{{ $sale->sale_number ?? $sale->id }}</title>
  <style>
    *{box-sizing:border-box}
    body{font-family:ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, 'Liberation Mono', 'Courier New', monospace; margin:0; padding:12px;}
    .rcpt{max-width:320px;margin:0 auto;color:#111}
    .c{text-align:center}
    .r{display:flex;justify-content:space-between;gap:8px}
    .muted{color:#4b5563}
    .sm{font-size:12px}
    .xs{font-size:11px}
    h1{font-size:16px;margin:0 0 4px 0}
    hr{border:none;border-top:1px dashed #999;margin:8px 0}
  </style>
</head>
<body>
  <div class="rcpt">
    <div class="c">
      <h1>{{ $storeName }}</h1>
      @if($storeAddress)<div class="xs muted">{{ $storeAddress }}</div>@endif
      @if($storePhone)<div class="xs muted">{{ $storePhone }}</div>@endif
      @if($website)<div class="xs muted">{{ $website }}</div>@endif
    </div>
    <hr/>
    <div class="xs muted">Receipt #: {{ $sale->sale_number ?? $sale->id }}</div>
    <div class="xs muted">Timestamp: {{ $ts }}</div>
    @if($register)<div class="xs muted">Register/Till: {{ $register }}</div>@endif
    <div class="xs muted">Customer Type: {{ $customerType }}</div>
    <hr/>
    @foreach($items as $x)
      <div class="r xs"><div>{{ $x['unitDisplay'] }} {{ $x['name'] }}</div><div>${{ number_format($x['lineTotal'], 2) }}</div></div>
      @if($x['discAmt'] > 0)
        <div class="r xs muted"><span>Discount</span><span>- ${{ number_format($x['discAmt'], 2) }}</span></div>
      @endif
    @endforeach
    <hr/>
    @if($itemDiscountTotal > 0)
      <div class="r xs"><span>Item Discounts</span><span>- ${{ number_format($itemDiscountTotal, 2) }}</span></div>
    @endif
    @if($cartDiscount > 0)
      <div class="r xs"><span>Cart Discount</span><span>- ${{ number_format($cartDiscount, 2) }}</span></div>
    @endif
    <div class="r xs"><span>Subtotal</span><span>${{ number_format($subtotal, 2) }}</span></div>
    <div class="r xs"><span>Tax</span><span>${{ number_format($tax, 2) }}</span></div>
    <div class="r xs"><span>Total</span><span>${{ number_format($total, 2) }}</span></div>
    <div class="r xs"><span>Change Due</span><span>${{ number_format($changeDue, 2) }}</span></div>
    <hr/>
    <div class="c xs">{!! nl2br(e($footer)) !!}</div>
  </div>
  <script>
    window.onload = function(){ try { if (new URLSearchParams(location.search).get('reprint')) window.print(); } catch(e){}
    };
  </script>
</body>
</html>
