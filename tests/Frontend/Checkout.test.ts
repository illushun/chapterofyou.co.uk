import Checkout from '@/pages/checkout/View.vue';
import { enableAutoUnmount, flushPromises, mount } from '@vue/test-utils';
import { afterEach, beforeEach, expect, it, vi } from 'vitest';

vi.mock('@/composables/useSeoHead', () => ({ useSeoHead: () => ({}) }));
vi.mock('@inertiajs/vue3', async (original) => ({
    ...(await original<typeof import('@inertiajs/vue3')>()),
    usePage: () => ({ props: { vatRegistered: false } }),
}));

enableAutoUnmount(afterEach);
afterEach(() => vi.unstubAllGlobals());
beforeEach(() => {
    vi.stubGlobal(
        'Stripe',
        vi.fn(() => ({
            elements: () => ({
                create: () => ({ mount: vi.fn(), destroy: vi.fn() }),
            }),
        })),
    );
    vi.stubGlobal(
        'fetch',
        vi.fn().mockResolvedValue({
            ok: true,
            json: async () => ({ clientSecret: 'test-secret' }),
        }),
    );
});

function checkout(isGuest = true) {
    return mount(Checkout, {
        props: {
            cartItems: [
                {
                    id: 1,
                    product_id: 1,
                    quantity: 2,
                    product: {
                        name: 'Candle',
                        cost: 10,
                        image_url: '/candle.jpg',
                    },
                },
            ],
            summary: {
                subtotal: 20,
                shipping: 3,
                total: 23,
                voucher_discount: 0,
                vat_component: 0,
            },
            addresses: [
                {
                    id: 1,
                    user_id: 1,
                    type: 'Home',
                    is_default: true,
                    line_1: '12 Rose Lane',
                    line_2: '',
                    city: 'London',
                    county: '',
                    postcode: 'SW1A 1AA',
                    country: 'United Kingdom',
                },
            ],
            appliedVoucher: null,
            giftVoucher: null,
            isGuest,
        },
        global: { stubs: { Footer: true, SeoHead: true } },
    });
}

it('lets guests review the total without hiding or resetting their address entry', async () => {
    const wrapper = checkout();
    await flushPromises();
    await wrapper.get('#addressLine1').setValue('24 New Road');
    const toggle = wrapper.get('.co-summary-toggle');
    expect(toggle.text()).toContain('£23.00');
    expect(toggle.attributes('aria-expanded')).toBe('false');
    await toggle.trigger('click');
    expect(toggle.attributes('aria-expanded')).toBe('true');
    await toggle.trigger('click');
    expect(
        (wrapper.get('#addressLine1').element as HTMLInputElement).value,
    ).toBe('24 New Road');
});

it('condenses the default address and retains its values when editing', async () => {
    const wrapper = checkout(false);
    await flushPromises();
    expect(wrapper.find('#addressLine1').exists()).toBe(false);
    expect(wrapper.get('.co-selected-address').text()).toContain(
        '12 Rose Lane',
    );
    const edit = wrapper
        .findAll('button')
        .find((button) => button.text() === 'Edit address');
    await edit!.trigger('click');
    expect(
        (wrapper.get('#addressLine1').element as HTMLInputElement).value,
    ).toBe('12 Rose Lane');
    expect(wrapper.findAll('form')).toHaveLength(1);
    expect(wrapper.get('.co-pay-btn').attributes('type')).toBe('submit');
});
