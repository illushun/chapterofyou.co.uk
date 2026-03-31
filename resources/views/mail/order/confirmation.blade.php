<x-mail::message>
# Order Confirmation #{{ $orderId }}

Hello {{ $firstName }},

Thank you for your order! Your items are being prepared and you will be notified once they are dispatched.

<x-mail::table>
| Item | Qty | Unit Price | Line Total |
| :--- | :--- | ---: | ---: |
@foreach ($items as $item)
| {{ $item['name'] }} | {{ $item['quantity'] }} | £{{ number_format($item['price'], 2) }} | £{{ number_format($item['total'], 2) }} |
@endforeach
</x-mail::table>

**Subtotal:** £{{ number_format($subtotal, 2) }}

**VAT:** £{{ number_format($tax, 2) }}

**Shipping:** £{{ number_format($shipping, 2) }}

@if ($voucherDiscount > 0)
**Discount:** -£{{ number_format($voucherDiscount, 2) }}
@endif

**Order Total: £{{ number_format($total, 2) }}**

---

<x-mail::panel>
**Shipping Address**

{{ $shippingAddress['name'] }}
{{ $shippingAddress['line1'] }}
@if ($shippingAddress['line2'])
{{ $shippingAddress['line2'] }}
@endif
{{ $shippingAddress['city'] }}@if($shippingAddress['state']), {{ $shippingAddress['state'] }}@endif
{{ $shippingAddress['zip'] }}
{{ $shippingAddress['country'] }}
</x-mail::panel>

<x-mail::button :url="route('order.confirmation', ['id' => $orderId])">
View Order
</x-mail::button>

If you have any questions about your order please email **contact@chapterofyou.co.uk**.

Thanks for shopping with me,
{{ config('app.name') }}
</x-mail::message>
