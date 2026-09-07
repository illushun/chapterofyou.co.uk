<script setup lang="ts">
import { router } from '@inertiajs/vue3';
import axios from 'axios';
import { computed, onBeforeUnmount, ref, watch } from 'vue';

interface SearchResult {
    type: 'product' | 'category' | 'journal';
    title: string;
    subtitle: string;
    url: string;
    image: string | null;
}

defineProps<{ compact?: boolean }>();
const emit = defineEmits<{ submitted: [] }>();
const query = ref('');
const results = ref<SearchResult[]>([]);
const isFocused = ref(false);
const isLoading = ref(false);
const activeIndex = ref(-1);
let timer: ReturnType<typeof setTimeout> | undefined;
let requestController: AbortController | undefined;
const showResults = computed(
    () => isFocused.value && query.value.trim().length >= 2,
);

watch(query, (value) => {
    clearTimeout(timer);
    requestController?.abort();
    activeIndex.value = -1;
    if (value.trim().length < 2) {
        results.value = [];
        isLoading.value = false;
        return;
    }
    timer = setTimeout(() => loadResults(value.trim()), 220);
});

async function loadResults(term: string) {
    requestController = new AbortController();
    isLoading.value = true;
    try {
        const response = await axios.get('/search/suggestions', {
            params: { q: term },
            signal: requestController.signal,
        });
        results.value = response.data.results;
    } catch (error: any) {
        if (error?.code !== 'ERR_CANCELED') results.value = [];
    } finally {
        isLoading.value = false;
    }
}

function submit() {
    if (activeIndex.value >= 0 && results.value[activeIndex.value]) {
        window.location.href = results.value[activeIndex.value].url;
        return;
    }
    const term = query.value.trim();
    router.get('/products', term ? { search: term } : {}, {
        preserveState: false,
    });
    emit('submitted');
}

function moveActive(direction: number) {
    if (!results.value.length) return;
    activeIndex.value =
        (activeIndex.value + direction + results.value.length) %
        results.value.length;
}

function close() {
    window.setTimeout(() => {
        isFocused.value = false;
    }, 120);
}

onBeforeUnmount(() => {
    clearTimeout(timer);
    requestController?.abort();
});
</script>

<template>
    <form
        class="search"
        :class="{ 'search--compact': compact }"
        role="search"
        @submit.prevent="submit"
        @keydown.down.prevent="moveActive(1)"
        @keydown.up.prevent="moveActive(-1)"
        @keydown.esc="isFocused = false"
    >
        <label
            :for="compact ? 'mobile-site-search' : 'desktop-site-search'"
            class="sr-only"
            >Search products, categories and journal posts</label
        >
        <svg aria-hidden="true" viewBox="0 0 24 24">
            <circle cx="11" cy="11" r="7" />
            <path d="m20 20-4-4" />
        </svg>
        <input
            :id="compact ? 'mobile-site-search' : 'desktop-site-search'"
            v-model="query"
            type="search"
            placeholder="Search scents, products and stories"
            autocomplete="off"
            aria-autocomplete="list"
            :aria-expanded="showResults"
            :aria-controls="
                compact ? 'mobile-search-results' : 'desktop-search-results'
            "
            @focus="isFocused = true"
            @blur="close"
        />
        <button type="submit">{{ compact ? 'Go' : 'Search' }}</button>

        <div
            v-if="showResults"
            :id="compact ? 'mobile-search-results' : 'desktop-search-results'"
            class="results"
            role="listbox"
        >
            <p v-if="isLoading" class="result-state">Searching…</p>
            <template v-else-if="results.length">
                <a
                    v-for="(result, index) in results"
                    :key="`${result.type}-${result.url}`"
                    :href="result.url"
                    class="result"
                    :class="{ 'result--active': activeIndex === index }"
                    role="option"
                    :aria-selected="activeIndex === index"
                >
                    <img v-if="result.image" :src="result.image" alt="" /><span
                        v-else
                        class="result-icon"
                        aria-hidden="true"
                        >{{
                            result.type === 'product'
                                ? '◇'
                                : result.type === 'category'
                                  ? '⌑'
                                  : '✦'
                        }}</span
                    >
                    <span
                        ><strong>{{ result.title }}</strong
                        ><small>{{ result.subtitle }}</small></span
                    ><b aria-hidden="true">→</b>
                </a>
                <button type="submit" class="view-all">
                    View all results for “{{ query.trim() }}”
                </button>
            </template>
            <p v-else class="result-state">
                No matching products, categories or stories.
            </p>
        </div>
    </form>
</template>

<style scoped>
.search {
    position: relative;
    height: 2.875rem;
    display: flex;
    align-items: center;
    color: var(--coy-color-text);
    background: var(--coy-color-page);
    border: 1px solid var(--coy-color-border);
    border-radius: var(--coy-radius-pill);
    transition:
        border-color 0.2s,
        box-shadow 0.2s;
}
.search:focus-within {
    border-color: var(--coy-color-focus);
    box-shadow: var(--coy-shadow-focus);
}
.search > svg {
    width: 1.25rem;
    flex: 0 0 auto;
    margin-left: 1rem;
    fill: none;
    stroke: currentColor;
    stroke-width: 1.8;
}
.search > input {
    min-width: 0;
    flex: 1;
    padding: 0.65rem 0.75rem;
    color: var(--coy-color-heading);
    background: transparent;
    border: 0;
    outline: 0;
    font: inherit;
    font-size: 1rem;
}
.search > input::placeholder {
    color: var(--coy-color-text);
    opacity: 0.8;
}
.search > button:not(.view-all) {
    height: 100%;
    padding: 0 1.25rem;
    color: var(--coy-color-on-accent);
    background: var(--coy-color-accent);
    border: 0;
    border-radius: 0 var(--coy-radius-pill) var(--coy-radius-pill) 0;
    font: inherit;
    font-size: 1rem;
    font-weight: 700;
    cursor: pointer;
}
.results {
    position: absolute;
    z-index: 50;
    top: calc(100% + 0.6rem);
    left: 0;
    right: 0;
    max-height: min(31rem, calc(100vh - 10rem));
    overflow: auto;
    padding: 0.5rem;
    background: var(--coy-color-surface);
    border: 1px solid var(--coy-color-border);
    border-radius: var(--coy-radius-md);
    box-shadow: var(--coy-shadow-md);
}
.result {
    display: grid;
    grid-template-columns: 3rem 1fr auto;
    align-items: center;
    gap: 0.75rem;
    padding: 0.65rem;
    color: var(--coy-color-heading);
    border-radius: var(--coy-radius-sm);
    text-decoration: none;
}
.result:hover,
.result--active {
    background: var(--coy-color-page);
}
.result img,
.result-icon {
    width: 3rem;
    height: 3rem;
    display: grid;
    place-items: center;
    object-fit: cover;
    background: var(--coy-color-champagne);
    border-radius: 0.4rem;
    font-size: 1.25rem;
}
.result > span:nth-child(2) {
    min-width: 0;
    display: flex;
    flex-direction: column;
}
.result strong {
    overflow: hidden;
    font-size: 1rem;
    text-overflow: ellipsis;
    white-space: nowrap;
}
.result small {
    color: var(--coy-color-text);
    font-size: 1rem;
}
.result > b {
    color: var(--coy-color-accent);
}
.result-state {
    margin: 0;
    padding: 1rem;
    color: var(--coy-color-text);
    font-size: 1rem;
}
.view-all {
    width: 100%;
    padding: 0.8rem;
    color: var(--coy-color-accent);
    background: transparent;
    border: 0;
    border-top: 1px solid var(--coy-color-border-soft);
    font: inherit;
    font-size: 1rem;
    font-weight: 700;
    text-align: center;
    cursor: pointer;
}
.sr-only {
    position: absolute;
    width: 1px;
    height: 1px;
    padding: 0;
    margin: -1px;
    overflow: hidden;
    clip: rect(0, 0, 0, 0);
    white-space: nowrap;
    border: 0;
}
.search--compact {
    height: 2.75rem;
}
.search--compact > button:not(.view-all) {
    padding-inline: 1rem;
}
.search--compact .results {
    top: calc(100% + 0.4rem);
    max-height: calc(100vh - 9rem);
}
</style>
