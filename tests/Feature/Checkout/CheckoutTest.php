<?php

namespace Tests\Feature\Checkout;

use App\Mail\Order\Confirmation;
use App\Mail\Order\NewOrderAlert;
use App\Models\Cart;
use App\Models\Courier;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use App\Models\Voucher;
use App\Services\Checkout\StripePayments;
use App\Services\VoucherService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Inertia\Testing\AssertableInertia as Assert;
use Mockery;
use PHPUnit\Framework\Attributes\DataProvider;
use Stripe\PaymentIntent;
use Tests\TestCase;

class CheckoutTest extends TestCase
{
    use RefreshDatabase;

    private Cart $cart;

    private Product $product;

    protected function setUp(): void
    {
        parent::setUp();
        Mail::fake();
        $user = User::factory()->create();
        $this->actingAs($user);
        $this->cart = Cart::create(['user_id' => $user->id]);
        $this->product = Product::create(['mpn' => 'TEST', 'name' => 'Test candle', 'description' => 'Candle', 'cost' => 19.99, 'stock_qty' => 10]);
        $courier = Courier::create(['name' => 'Test courier', 'cost' => 2.99]);
        $this->product->courier()->create(['courier_id' => $courier->id, 'per_item' => 'no']);
        $this->cart->items()->create(['product_id' => $this->product->id, 'quantity' => 1]);
    }

    public function test_successful_checkout_saves_the_order_and_queues_notifications(): void
    {
        $this->payment();
        $this->postPayment()->assertRedirect(route('order.confirmation', ['id' => Order::firstOrFail()->id]));
        $this->assertDatabaseHas('order', ['grand_total' => 22.98, 'status' => 'successful']);
        $this->assertDatabaseHas('order_item', ['quantity' => 1, 'product_cost' => 19.99]);
        $this->assertDatabaseCount('cart_item', 0);
        Mail::assertQueued(Confirmation::class);
        Mail::assertQueued(NewOrderAlert::class);
    }

    public function test_checkout_includes_the_enabled_product_thumbnail(): void
    {
        $this->product->images()->create(['image' => '/storage/disabled.jpg', 'status' => 'disabled']);
        $this->product->images()->create(['image' => '/storage/candle.jpg', 'status' => 'enabled']);

        $this->get(route('checkout.index'))->assertOk()->assertInertia(fn (Assert $page) => $page
            ->component('checkout/View')
            ->where('cartItems.0.product.image_url', '/storage/candle.jpg'));
    }

    public function test_retry_returns_the_existing_order_without_sending_more_mail(): void
    {
        $this->payment();
        $this->postPayment()->assertSessionHasNoErrors();
        $this->postPayment()->assertRedirect(route('order.confirmation', ['id' => Order::firstOrFail()->id]));
        $this->assertDatabaseCount('order', 1);
        Mail::assertQueued(Confirmation::class, 1);
        Mail::assertQueued(NewOrderAlert::class, 1);
    }

    #[DataProvider('invalidPayments')]
    public function test_invalid_payment_does_not_create_an_order(array $changes): void
    {
        $this->payment($changes);
        $this->postPayment()->assertSessionHas('error');
        $this->assertDatabaseCount('order', 0);
        $this->assertDatabaseCount('cart_item', 1);
        Mail::assertNothingOutgoing();
    }

    public static function invalidPayments(): array
    {
        return [
            'wrong cart' => [['metadata' => ['cart_id' => '999999']]],
            'wrong currency' => [['currency' => 'usd']],
            'not paid' => [['status' => 'requires_payment_method']],
            'wrong amount' => [['amount' => 1]],
        ];
    }

    public function test_discount_is_recalculated_from_the_current_cart(): void
    {
        $voucher = $this->voucher();
        $this->withSession(['voucher' => ['code' => $voucher->code, 'discount' => 15]]);
        $this->mock(StripePayments::class)->shouldReceive('create')->once()
            ->withArgs(fn ($parameters) => $parameters['amount'] === 2098)
            ->andReturn(PaymentIntent::constructFrom(['id' => 'pi_test', 'client_secret' => 'secret']));
        $this->postJson(route('checkout.payment_intent'))->assertOk();
        $this->assertEquals(2.00, session('voucher.discount'));
    }

    public function test_expired_voucher_cannot_initialize_payment(): void
    {
        $voucher = $this->voucher(['valid_until' => now()->subDay()]);
        $this->withSession(['voucher' => ['code' => $voucher->code, 'discount' => 2]]);
        $this->mock(StripePayments::class)->shouldNotReceive('create');
        $this->postJson(route('checkout.payment_intent'))->assertUnprocessable();
    }

    public function test_missing_shipping_configuration_cannot_silently_create_a_payment(): void
    {
        $this->product->courier()->delete();
        $this->mock(StripePayments::class)->shouldNotReceive('create');
        $this->postJson(route('checkout.payment_intent'))->assertUnprocessable();
        $this->get(route('checkout.index'))->assertRedirect(route('cart.view'));
    }

    public function test_failure_recording_voucher_usage_rolls_back_order_and_cart_changes(): void
    {
        $voucher = $this->voucher();
        $this->withSession(['voucher' => ['code' => $voucher->code, 'discount' => 2]]);
        $this->payment(['amount' => 2098]);
        $service = Mockery::mock(VoucherService::class)->makePartial();
        $service->shouldReceive('recordUsage')->once()->andThrow(new \RuntimeException('Storage failure'));
        $this->app->instance(VoucherService::class, $service);
        $this->postPayment()->assertSessionHas('error');
        $this->assertDatabaseCount('order', 0);
        $this->assertDatabaseCount('order_item', 0);
        $this->assertDatabaseCount('cart_item', 1);
        $this->assertDatabaseCount('voucher_usage', 0);
        $this->assertNotNull(session('voucher'));
        Mail::assertNothingOutgoing();
    }

    public function test_mail_queue_failure_does_not_turn_a_completed_order_into_a_failed_checkout(): void
    {
        $this->payment();
        Mail::shouldReceive('to')->andThrow(new \RuntimeException('Queue unavailable'));
        $this->postPayment()->assertRedirect(route('order.confirmation', ['id' => Order::firstOrFail()->id]));
        $this->assertDatabaseCount('order', 1);
        $this->assertDatabaseCount('cart_item', 0);
    }

    public function test_guest_can_confirm_and_retry_their_order(): void
    {
        auth()->logout();
        $this->cart->update(['user_id' => null, 'session_id' => 'guest-cart']);
        $this->withSession(['cart_session_id' => 'guest-cart']);
        $this->payment();
        $this->postPayment()->assertSessionHas('guest_order_id');
        $order = Order::firstOrFail();
        $this->get(route('order.confirmation', ['id' => $order->id]))->assertOk();
        $this->postPayment()->assertRedirect(route('order.confirmation', ['id' => $order->id]));
        $this->assertDatabaseCount('order', 1);
    }

    public function test_gift_voucher_checkout_creates_the_voucher_after_payment(): void
    {
        $this->cart->items()->delete();
        $this->withSession(['pending_gift_voucher' => ['amount' => 25, 'delivery_type' => 'email', 'recipient_name' => 'Recipient', 'recipient_email' => 'recipient@example.com']]);
        $this->payment(['amount' => 2500]);
        $this->postPayment()->assertSessionHas('success');
        $this->assertDatabaseHas('gift_voucher_order', ['amount' => 25, 'recipient_email' => 'recipient@example.com']);
        $this->assertDatabaseCount('vouchers', 1);
        $this->assertNull(session('pending_gift_voucher'));
        Mail::assertSent(\App\Mail\GiftVoucher::class);
    }

    public function test_guest_invoice_requires_the_matching_guest_order_session(): void
    {
        auth()->logout();
        $this->cart->update(['user_id' => null, 'session_id' => 'guest-cart']);
        $this->withSession(['cart_session_id' => 'guest-cart']);
        $this->payment();
        $this->postPayment();
        $order = Order::firstOrFail();
        $this->get(route('orders.invoice.download', $order))->assertOk()->assertHeader('content-type', 'application/pdf');
        $this->withSession(['guest_order_id' => $order->id + 1]);
        $this->get(route('orders.invoice.download', $order))->assertForbidden();
    }

    public function test_another_customer_cannot_view_the_order_or_invoice(): void
    {
        $this->payment();
        $this->postPayment();
        $order = Order::firstOrFail();
        $this->actingAs(User::factory()->create());
        $this->get(route('order.confirmation', ['id' => $order->id]))->assertNotFound();
        $this->get(route('orders.invoice.download', $order))->assertForbidden();
    }

    private function payment(array $changes = []): void
    {
        $intent = PaymentIntent::constructFrom(array_replace([
            'id' => 'pi_test', 'status' => 'succeeded', 'amount' => 2298, 'currency' => 'gbp',
            'metadata' => ['cart_id' => (string) $this->cart->id], 'payment_method_types' => ['card'],
        ], $changes));
        $this->mock(StripePayments::class)->shouldReceive('retrieve')->with('pi_test')->andReturn($intent);
    }

    private function postPayment()
    {
        return $this->post(route('checkout.process_payment'), [
            'paymentIntentId' => 'pi_test', 'paymentType' => 'card', 'email' => 'buyer@example.com',
            'fullName' => 'Test Buyer', 'addressLine1' => '1 Test Street', 'city' => 'London',
            'postcode' => 'SW1A 1AA', 'country' => 'United Kingdom',
        ]);
    }

    private function voucher(array $attributes = []): Voucher
    {
        return Voucher::create(array_replace(['code' => 'TEST10', 'type' => 'percentage', 'value' => 10, 'is_active' => true], $attributes));
    }
}
