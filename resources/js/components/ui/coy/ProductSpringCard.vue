<script setup lang="ts">
import { computed, ref, watch } from 'vue';

interface ProductCardData { id: number; name: string; mpn: string; cost: number; stock_qty: number; images?: { image: string }[]; total_unique_views?: number; scent_families?: string | null; seo?: { slug: string } }
const props = defineProps<{ product: ProductCardData; className?: string; wishlisted?: boolean }>();
const emit = defineEmits(['addToCart', 'favourite']);
const isWishlisted = ref(props.wishlisted ?? false);
watch(() => props.wishlisted, (value) => { isWishlisted.value = value ?? false; });
const isPopular = computed(() => (props.product.total_unique_views || 0) > 100);
const isLowStock = computed(() => props.product.stock_qty > 0 && props.product.stock_qty <= 5);
const imageUrl = computed(() => props.product.images?.[0]?.image);
const scentLabel = computed(() => {
    const families = props.product.scent_families?.split(',').map((family) => family.trim().replaceAll('_', ' ').replace(/\b\w/g, (letter) => letter.toUpperCase())).filter(Boolean).slice(0, 2);
    if (families?.length) return families.join(' · ');
    return props.product.name.toLowerCase().includes('refill') ? 'Reed diffuser refill' : 'Handmade reed diffuser';
});
const price = computed(() => `£${Number(props.product.cost).toFixed(2)}`);
const productLink = computed(() => props.product.seo?.slug ? `/product/${props.product.seo.slug}` : `/product/${props.product.id}`);

function toggleFavourite() {
    isWishlisted.value = !isWishlisted.value;
    emit('favourite', props.product.id);
}
</script>

<template>
    <article class="product-card" :class="className">
        <div class="product-visual">
            <a :href="productLink" :aria-label="`View ${product.name}`">
                <img v-if="imageUrl" :src="imageUrl" :alt="product.name" loading="lazy" />
                <span v-else class="placeholder">Chapter of You</span>
            </a>
            <span v-if="isLowStock" class="badge badge--stock">Only {{ product.stock_qty }} left</span>
            <span v-else-if="isPopular" class="badge">Popular</span>
            <button type="button" class="wishlist" :class="{ 'wishlist--active': isWishlisted }" :aria-label="isWishlisted ? `Remove ${product.name} from wishlist` : `Add ${product.name} to wishlist`" @click="toggleFavourite">
                <svg viewBox="0 0 24 24" :fill="isWishlisted ? 'currentColor' : 'none'" stroke="currentColor" stroke-width="2"><path d="M20.8 4.6a5.5 5.5 0 0 0-7.8 0L12 5.7l-1.1-1.1a5.5 5.5 0 0 0-7.8 7.8l1.1 1.1 7.8 7.7 7.8-7.7 1-1.1a5.5 5.5 0 0 0 0-7.8Z" /></svg>
            </button>
            <div v-if="product.stock_qty <= 0" class="out-of-stock">Out of stock</div>
        </div>
        <div class="product-body">
            <a :href="productLink"><h3>{{ product.name }}</h3></a>
            <p>{{ scentLabel }}</p>
            <div class="product-footer">
                <strong>{{ price }}</strong>
                <button type="button" :disabled="product.stock_qty <= 0" @click="$emit('addToCart', product.id)">
                    <svg aria-hidden="true" viewBox="0 0 24 24"><path d="M6 2 3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4ZM3 6h18M16 10a4 4 0 0 1-8 0" /></svg>
                    {{ product.stock_qty > 0 ? 'Add to basket' : 'Unavailable' }}
                </button>
            </div>
        </div>
    </article>
</template>

<style scoped>
.product-card{height:100%;display:flex;flex-direction:column;color:var(--coy-color-text);background:var(--coy-color-surface);border:1px solid var(--coy-color-border);border-radius:var(--coy-radius-md);overflow:hidden;transition:border-color .2s,box-shadow .2s}.product-card:hover{border-color:var(--coy-color-rose-gold);box-shadow:var(--coy-shadow-md)}.product-visual{position:relative;aspect-ratio:1/1.05;overflow:hidden;background:var(--coy-color-champagne)}.product-visual>a{display:block;width:100%;height:100%}.product-visual img{width:100%;height:100%;display:block;object-fit:cover;transition:transform .45s var(--coy-ease)}.product-card:hover .product-visual img{transform:scale(1.025)}.placeholder{width:100%;height:100%;display:grid;place-items:center;color:var(--coy-color-heading);font-family:var(--coy-font-display);font-size:1.35rem}.badge{position:absolute;top:.75rem;left:.75rem;padding:.4rem .7rem;color:var(--coy-color-on-accent);background:var(--coy-color-accent);border-radius:var(--coy-radius-pill);font-size:1rem;font-weight:700;line-height:1}.badge--stock{background:#805022}.wishlist{position:absolute;top:.65rem;right:.65rem;width:2.75rem;height:2.75rem;display:grid;place-items:center;color:var(--coy-color-accent);background:rgb(255 253 251/92%);border:1px solid var(--coy-color-border);border-radius:50%;cursor:pointer}.wishlist:hover,.wishlist--active{background:var(--coy-color-blush)}.wishlist svg{width:1.25rem}.out-of-stock{position:absolute;inset:auto 0 0;padding:.7rem;color:var(--coy-color-heading);background:rgb(250 246 242/92%);font-size:1rem;font-weight:700;text-align:center}.product-body{display:flex;flex:1;flex-direction:column;padding:1.15rem}.product-body>a{color:var(--coy-color-heading);text-decoration:none}.product-body h3{margin:0;font-family:var(--coy-font-display);font-size:1.4rem;font-weight:600;line-height:1.2}.product-body>p{margin:.35rem 0 1.2rem;font-size:1rem;line-height:1.45}.product-footer{display:flex;align-items:center;justify-content:space-between;gap:.75rem;margin-top:auto}.product-footer>strong{color:var(--coy-color-heading);font-size:1.125rem}.product-footer button{min-height:2.75rem;display:inline-flex;align-items:center;justify-content:center;gap:.4rem;padding:.55rem .85rem;color:var(--coy-color-on-accent);background:var(--coy-color-accent);border:1px solid var(--coy-color-accent);border-radius:var(--coy-radius-pill);font:inherit;font-size:1rem;font-weight:700;cursor:pointer}.product-footer button:hover:not(:disabled){background:var(--coy-color-accent-hover)}.product-footer button:disabled{opacity:.55;cursor:not-allowed}.product-footer svg{width:1rem;fill:none;stroke:currentColor;stroke-width:2;stroke-linecap:round;stroke-linejoin:round}
@media(max-width:600px){.product-visual{aspect-ratio:1/1}.product-body{padding:1rem}.product-body h3{font-size:1.25rem}.product-footer{align-items:stretch;flex-direction:column}.product-footer button{width:100%}}
</style>
