<x-mail::message>

Order Confirmation: Your Order #{{ $orderId }}

Hello,

Thank you for your order! Your items are being prepared for shipment and you will be notified as soon as they are ready.

Below are the details of your purchase:

<x-mail::table>
| Item | Quantity | Price |
| :------------- | :------------- | :------------- |
@foreach ($items as $item)
| {{ $item['name'] }} | {{ $item['quantity'] }} | ${{ number_format($item['price'], 2) }} |
@endforeach
</x-mail::table>

Subtotal: ${{ number_format($subtotal, 2) }}
Shipping: ${{ number_format($shipping, 2) }}
Tax: ${{ number_format($tax, 2) }}
**Order Total:** **${{ number_format($total, 2) }}**

<x-mail::panel>

Shipping Address

{{ $shippingAddress['name'] }}




{{ $shippingAddress['line1'] }}




@if ($shippingAddress['line2']){{ $shippingAddress['line2'] }}



@endif
{{ $shippingAddress['city'] }}, {{ $shippingAddress['state'] }} {{ $shippingAddress['zip'] }}




{{ $shippingAddress['country'] }}
</x-mail::panel>

<x-mail::button :url="route('account.order.view', $orderId)">
View Order Details
</x-mail::button>

If you have any questions regarding your order, please reply directly to this email.

Thanks for shopping with us,




{{ config('app.name') }}
</x-mail::message>
