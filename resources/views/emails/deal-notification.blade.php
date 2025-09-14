<!DOCTYPE html>
<html>
  <head>
    <meta charset="utf-8">
    <meta http-equiv="x-ua-compatible" content="ie=edge">
    <title>{{ $deal['name'] ?? 'Deal' }} at {{ config('app.name', 'Cannabis POS') }}</title>
  </head>
  <body style="font-family: Arial, sans-serif; color: #111;">
    <div style="max-width: 640px; margin: 0 auto; padding: 16px;">
      <h2 style="margin: 0 0 8px 0;">{{ $deal['name'] ?? 'New Deal' }}</h2>
      @if(!empty($deal['description']))
        <p style="margin: 0 0 12px 0;">{{ $deal['description'] }}</p>
      @endif
      <p style="margin: 0 0 12px 0;">
        Discount: 
        @if(($deal['type'] ?? '') === 'percentage')
          {{ number_format((float)($deal['value'] ?? 0), 0) }}%
        @elseif(($deal['type'] ?? '') === 'fixed_amount')
          ${{ number_format((float)($deal['value'] ?? 0), 2) }} off
        @elseif(($deal['type'] ?? '') === 'bogo')
          BOGO {{ number_format((float)($deal['value'] ?? 0), 0) }}%
        @elseif(($deal['type'] ?? '') === 'bulk')
          {{ number_format((float)($deal['value'] ?? 0), 0) }}% Bulk
        @else
          {{ number_format((float)($deal['value'] ?? 0), 0) }}%
        @endif
      </p>
      @php
        $start = isset($deal['start_date']) && $deal['start_date'] ? \Carbon\Carbon::parse($deal['start_date'])->format('M j, Y') : null;
        $end = isset($deal['end_date']) && $deal['end_date'] ? \Carbon\Carbon::parse($deal['end_date'])->format('M j, Y') : null;
      @endphp
      @if($start || $end)
        <p style="margin: 0 0 12px 0;">Valid {{ $start ? 'from '.$start : '' }} {{ $end ? 'to '.$end : '' }}</p>
      @endif
      @if(!empty($deal['applicable_categories']) && is_array($deal['applicable_categories']))
        <p style="margin: 0 0 12px 0;">Categories: {{ implode(', ', $deal['applicable_categories']) }}</p>
      @endif
      @if(!empty($customer['first_name']) || !empty($customer['last_name']))
        <p style="margin: 16px 0 0 0;">Hi {{ trim(($customer['first_name'] ?? '').' '.($customer['last_name'] ?? '')) }}, thanks for being a valued customer.</p>
      @endif
      <p style="margin: 16px 0 0 0;">See you soon!</p>
    </div>
  </body>
</html>
