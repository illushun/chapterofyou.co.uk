import ProductFaqEditor from '@/components/admin/product/ProductFaqEditor.vue';
import ProductImagesEditor from '@/components/admin/product/ProductImagesEditor.vue';
import ProductUsageEditor from '@/components/admin/product/ProductUsageEditor.vue';
import ProductFaqAccordion from '@/components/product/ProductFaqAccordion.vue';
import ProductGallery from '@/components/product/ProductGallery.vue';
import ModalImageViewer from '@/components/ui/coy/ModalImageViewer.vue';
import ProductEditor from '@/pages/admin/product/CreateEdit.vue';
import { router } from '@inertiajs/vue3';
import { enableAutoUnmount, mount, type DOMWrapper } from '@vue/test-utils';
import { afterEach, beforeEach, describe, expect, it, vi } from 'vitest';
import { defineComponent, nextTick, reactive } from 'vue';

vi.mock('@inertiajs/vue3', async (importOriginal) => ({
    ...(await importOriginal<typeof import('@inertiajs/vue3')>()),
    Head: { render: () => null },
}));

enableAutoUnmount(afterEach);
afterEach(() => vi.unstubAllGlobals());

const existingImage = {
    id: 7,
    product_id: 1,
    image: '/candle.jpg',
    file_path: '/candle.jpg',
    status: 'enabled',
    is_enabled: true,
};

function imageEditor() {
    const state = reactive({
        files: [] as File[],
        deleted: [] as number[],
        toggled: [] as number[],
    });
    const wrapper = mount(
        defineComponent({
            components: { ProductImagesEditor },
            setup: () => ({ state, images: [existingImage] }),
            template:
                '<ProductImagesEditor :images="images" v-model:new-images="state.files" v-model:deleted-ids="state.deleted" v-model:toggled-ids="state.toggled" />',
        }),
    );
    return { state, wrapper };
}

async function upload(
    input: Pick<DOMWrapper<Element>, 'element' | 'trigger'>,
    files: File[],
) {
    Object.defineProperty(input.element, 'files', {
        value: files,
        configurable: true,
    });
    await input.trigger('change');
}

function imageFiles(count: number) {
    return Array.from(
        { length: count },
        (_, i) => new File(['image'], `image-${i}.png`, { type: 'image/png' }),
    );
}

describe('image viewer', () => {
    it('moves focus into the dialog and restores it when closed', async () => {
        const trigger = document.createElement('button');
        document.body.appendChild(trigger);
        trigger.focus();
        const wrapper = mount(ModalImageViewer, {
            props: {
                images: [{ image: '/one.jpg' }, { image: '/two.jpg' }],
                initialIndex: 1,
                open: true,
            },
            attachTo: document.body,
        });
        await nextTick();
        expect(document.activeElement).toBe(wrapper.get('.miv-close').element);
        expect(wrapper.get('.miv-img').attributes('src')).toBe('/two.jpg');
        await wrapper.get('.miv-close').trigger('click');
        await wrapper.setProps({ open: false });
        await nextTick();
        expect(document.activeElement).toBe(trigger);
        trigger.remove();
    });
});

describe('product image editor', () => {
    it('keeps the upload queue capped at five and allows replacing a removed file', async () => {
        const { state, wrapper } = imageEditor();
        const files = imageFiles(6);
        await upload(wrapper.get('input[type="file"]'), files);
        expect(state.files).toEqual(files.slice(0, 5));
        expect(wrapper.findAll('.pe-queue-item')).toHaveLength(5);
        await wrapper.get('.pe-queue-item button').trigger('click');
        await upload(wrapper.get('input[type="file"]'), [files[5]]);
        expect(state.files).toEqual(files.slice(1));
    });

    it('toggles visibility reversibly and clears a pending toggle when deleting', async () => {
        const { state, wrapper } = imageEditor();
        await wrapper.get('button[title="Hide"]').trigger('click');
        expect(state.toggled).toEqual([7]);
        expect(wrapper.get('.pe-img-card').classes()).toContain(
            'pe-img-card--disabled',
        );
        await wrapper.get('button[title="Show"]').trigger('click');
        expect(state.toggled).toEqual([]);
        await wrapper.get('button[title="Hide"]').trigger('click');
        await wrapper.get('button[title="Delete"]').trigger('click');
        expect(state.deleted).toEqual([7]);
        expect(state.toggled).toEqual([]);
        expect(wrapper.find('.pe-img-card').exists()).toBe(false);
        expect(existingImage.is_enabled).toBe(true);
    });

    it('shows server-side upload errors', () => {
        const wrapper = mount(ProductImagesEditor, {
            props: {
                images: [],
                newImages: [],
                deletedIds: [],
                toggledIds: [],
                error: 'The image is too large.',
            },
        });
        expect(wrapper.get('.adm-err').text()).toBe('The image is too large.');
    });
});

describe('product content editors', () => {
    it('adds, edits, and removes FAQs in the parent form state', async () => {
        const state = reactive({
            faqs: [] as { question: string; answer: string }[],
        });
        const wrapper = mount(
            defineComponent({
                components: { ProductFaqEditor },
                setup: () => ({ state }),
                template: '<ProductFaqEditor v-model="state.faqs" />',
            }),
        );
        await wrapper.get('.pe-dashed-btn').trigger('click');
        await wrapper.get('input').setValue('How long does it last?');
        await wrapper.get('textarea').setValue('Several weeks.');
        expect(state.faqs).toEqual([
            { question: 'How long does it last?', answer: 'Several weeks.' },
        ]);
        await wrapper.get('button[aria-label="Remove FAQ"]').trigger('click');
        expect(state.faqs).toEqual([]);
        expect(wrapper.text()).toContain('No FAQs added yet.');
    });

    it('keeps the usage preview and parent value in sync', async () => {
        const state = reactive({ instructions: '' });
        const wrapper = mount(
            defineComponent({
                components: { ProductUsageEditor },
                setup: () => ({ state }),
                template:
                    '<ProductUsageEditor v-model="state.instructions" error="Please check the instructions." />',
            }),
        );
        expect(wrapper.find('.pe-preview').exists()).toBe(false);
        await wrapper.get('textarea').setValue('<p>Turn the reeds weekly.</p>');
        expect(state.instructions).toBe('<p>Turn the reeds weekly.</p>');
        expect(wrapper.get('.pe-preview-body p').text()).toBe(
            'Turn the reeds weekly.',
        );
        expect(wrapper.get('.adm-err').text()).toBe(
            'Please check the instructions.',
        );
        await wrapper.get('textarea').setValue('   ');
        expect(wrapper.find('.pe-preview').exists()).toBe(false);
    });
});

describe('storefront product sections', () => {
    it('selects thumbnails, updates the main image, and requests the full viewer', async () => {
        const state = reactive({ index: 0 });
        const onOpen = vi.fn();
        const wrapper = mount(
            defineComponent({
                components: { ProductGallery },
                setup: () => ({
                    state,
                    onOpen,
                    images: [{ image: '/one.jpg' }, { image: '/two.jpg' }],
                }),
                template:
                    '<ProductGallery :images="images" name="Lavender" :popular="true" v-model:selected-index="state.index" @open="onOpen" />',
            }),
        );
        await wrapper.findAll('.pd-thumb')[1].trigger('click');
        expect(state.index).toBe(1);
        expect(wrapper.get('.pd-main-img').attributes('src')).toBe('/two.jpg');
        expect(wrapper.get('.pd-popular-badge').text()).toBe('Popular');
        await wrapper.get('.pd-main-img-btn').trigger('click');
        expect(onOpen).toHaveBeenCalledOnce();
    });

    it('shows a placeholder without opening an empty viewer', async () => {
        const wrapper = mount(ProductGallery, {
            props: {
                images: [],
                name: 'Lavender',
                popular: false,
                selectedIndex: 0,
            },
        });
        expect(wrapper.get('.pd-main-img').attributes('src')).toBe(
            '/images/placeholder.jpg',
        );
        expect(wrapper.find('.pd-thumbs').exists()).toBe(false);
        await wrapper.get('.pd-main-img-btn').trigger('click');
        expect(wrapper.emitted('open')).toBeUndefined();
    });

    it('keeps only one FAQ expanded and synchronizes accessibility attributes', async () => {
        const wrapper = mount(ProductFaqAccordion, {
            props: {
                faqs: [
                    { question: 'First?', answer: 'One.' },
                    { question: 'Second?', answer: 'Two.' },
                ],
            },
        });
        const buttons = wrapper.findAll('button');
        await buttons[0].trigger('click');
        expect(buttons[0].attributes('aria-expanded')).toBe('true');
        expect(
            wrapper.get('#' + buttons[0].attributes('aria-controls')).classes(),
        ).toContain('pd-faq-body--open');
        await buttons[1].trigger('click');
        expect(buttons[0].attributes('aria-expanded')).toBe('false');
        expect(buttons[1].attributes('aria-expanded')).toBe('true');
        await buttons[1].trigger('click');
        expect(wrapper.findAll('.pd-faq-body--open')).toHaveLength(0);
    });
});

describe('product editor submission', () => {
    const testRoute = (name: string, id?: number) => `/${name}/${id ?? ''}`;
    beforeEach(() => vi.stubGlobal('route', testRoute));

    it('submits changes from every extracted editor and resets uploads after success', async () => {
        const post = vi.spyOn(router, 'post').mockImplementation(() => {});
        const wrapper = mount(ProductEditor, {
            props: {
                product: {
                    id: 1,
                    mpn: 'LAV',
                    name: 'Lavender',
                    description: 'A diffuser',
                    details: '',
                    how_to_use: '',
                    status: 'enabled',
                    cost: 20,
                    stock_qty: 5,
                    parent_product_id: null,
                    seo: {
                        slug: 'lavender',
                        meta_title: 'Lavender',
                        meta_description: '',
                    },
                },
                categories: [],
                couriers: [],
                parentProducts: [],
                refillCandidates: [],
                selectedCategoryIds: [],
                selectedCourierId: null,
                courierPerItem: 'no',
                oils: [],
                productMaterials: [],
                productFaqs: [],
                productImages: [existingImage],
                isEditing: true,
                errors: {},
            },
            global: {
                mocks: { route: testRoute },
                stubs: {
                    AdminLayout: { template: '<div><slot /></div>' },
                    Head: true,
                    Link: true,
                },
            },
        });
        const file = imageFiles(1)[0];
        await upload(wrapper.get('input[type="file"]'), [file]);
        await wrapper.get('button[title="Hide"]').trigger('click');
        const faq = wrapper.getComponent(ProductFaqEditor);
        await faq.get('.pe-dashed-btn').trigger('click');
        await faq.get('input').setValue('Where should I place it?');
        await faq.get('textarea').setValue('On a stable surface.');
        await wrapper
            .getComponent(ProductUsageEditor)
            .get('textarea')
            .setValue('<p>Insert the reeds.</p>');
        await wrapper.get('form').trigger('submit');
        expect(post).toHaveBeenCalledOnce();
        expect(post.mock.calls[0][1]).toMatchObject({
            _method: 'put',
            new_images: [file],
            images_to_toggle: [7],
            images_to_delete: [],
            how_to_use: '<p>Insert the reeds.</p>',
            faqs: [
                {
                    question: 'Where should I place it?',
                    answer: 'On a stable surface.',
                },
            ],
        });
        await post.mock.calls[0][2]?.onSuccess?.({} as never);
        await nextTick();
        expect(wrapper.findAll('.pe-queue-item')).toHaveLength(0);
    });
});
