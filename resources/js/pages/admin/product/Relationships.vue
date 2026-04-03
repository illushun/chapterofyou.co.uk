<script setup lang="ts">
import AdminLayout from '@/layouts/AdminLayout.vue';
import { Head, router, Link } from '@inertiajs/vue3';
import { computed, ref } from 'vue';

declare const route: any;

interface ProductNode {
    id: number;
    name: string;
    parent_id: number | null;
    mpn?: string;
    status?: string;
    stock_qty?: number;
}

interface TreeNode extends ProductNode {
    children: TreeNode[];
    collapsed: boolean;
}

const props = defineProps<{
    productsData: ProductNode[];
}>();

const parentId = ref<number | null>(null);
const childId = ref<number | null>(null);
const submitting = ref(false);
const searchQuery = ref('');
const flash = ref<string | null>(null);
const collapsed = ref<Record<number, boolean>>({});

const toggleCollapse = (id: number) => {
    collapsed.value[id] = !collapsed.value[id];
};

const buildTree = (items: ProductNode[]): TreeNode[] => {
    const map: Record<number, TreeNode> = {};
    const roots: TreeNode[] = [];
    items.forEach(item => { map[item.id] = { ...item, children: [], collapsed: false }; });
    items.forEach(item => {
        if (item.parent_id && map[item.parent_id]) map[item.parent_id].children.push(map[item.id]);
        else if (!item.parent_id) roots.push(map[item.id]);
    });
    return roots;
};

const filteredTree = computed(() => {
    if (!searchQuery.value.trim()) return buildTree(props.productsData);
    const q = searchQuery.value.toLowerCase();
    const matchingIds = new Set<number>();
    const ancestorIds = new Set<number>();
    props.productsData.forEach(p => {
        if (p.name.toLowerCase().includes(q) || String(p.id).includes(q)) {
            matchingIds.add(p.id);
            let current = p;
            while (current.parent_id) {
                ancestorIds.add(current.parent_id);
                const parent = props.productsData.find(x => x.id === current.parent_id);
                if (!parent) break;
                current = parent;
            }
        }
    });
    return buildTree(props.productsData.filter(p => matchingIds.has(p.id) || ancestorIds.has(p.id)));
});

const totalProducts = computed(() => props.productsData.length);
const parentProducts = computed(() => props.productsData.filter(p => !p.parent_id).length);
const childProducts = computed(() => props.productsData.filter(p => p.parent_id).length);
const orphans = computed(() => {
    const parentIds = new Set(props.productsData.filter(p => !p.parent_id).map(p => p.id));
    return props.productsData.filter(p => p.parent_id && !parentIds.has(p.parent_id)).length;
});

const childOptions = computed(() => props.productsData.filter(p => p.id !== parentId.value));

function assignRelationship() {
    if (!parentId.value || !childId.value) return;
    submitting.value = true;
    router.post(route('admin.products.assign-relationship'),
        { parent_id: parentId.value, child_id: childId.value },
        {
            preserveScroll: true,
            onSuccess: () => {
                flash.value = 'Relationship assigned successfully.';
                parentId.value = null;
                childId.value = null;
                setTimeout(() => { flash.value = null; }, 3000);
            },
            onFinish: () => { submitting.value = false; },
        }
    );
}

function removeRelationship(productId: number) {
    router.post(route('admin.products.remove-relationship'), { product_id: productId }, { preserveScroll: true });
}
</script>

<template>
    <AdminLayout>

        <Head title="Product Relationships — Admin" />

        <!-- Header -->
        <div class="rp-header">
            <div>
                <div class="rp-breadcrumb">
                    <Link :href="route('admin.products.index')" class="rp-breadcrumb-link">Products</Link>
                    <span class="rp-breadcrumb-sep">/</span>
                    <span>Relationships</span>
                </div>
                <h1 class="rp-title">Product Relationships</h1>
                <p class="rp-sub">Manage parent–child product hierarchies</p>
            </div>
        </div>

        <!-- Flash -->
        <Transition name="rp-fade">
            <div v-if="flash" class="rp-flash">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"
                    stroke-linecap="round" stroke-linejoin="round">
                    <path d="M20 6 9 17l-5-5" />
                </svg>
                {{ flash }}
            </div>
        </Transition>

        <!-- Stats -->
        <div class="rp-stats">
            <div class="rp-stat">
                <p class="rp-stat-val">{{ totalProducts }}</p>
                <p class="rp-stat-label">Total</p>
            </div>
            <div class="rp-stat rp-stat--blush">
                <p class="rp-stat-val">{{ parentProducts }}</p>
                <p class="rp-stat-label">Parents</p>
            </div>
            <div class="rp-stat rp-stat--lav">
                <p class="rp-stat-val">{{ childProducts }}</p>
                <p class="rp-stat-label">Children</p>
            </div>
            <div class="rp-stat" :class="orphans > 0 ? 'rp-stat--warn' : 'rp-stat--sage'">
                <p class="rp-stat-val">{{ orphans }}</p>
                <p class="rp-stat-label">Orphaned</p>
            </div>
        </div>

        <!-- Grid -->
        <div class="rp-grid">

            <!-- Left sidebar -->
            <div class="rp-sidebar">

                <!-- Assign form -->
                <section class="rp-card">
                    <h2 class="rp-card-title">Assign Relationship</h2>
                    <p class="rp-card-hint">Link a child product to a parent.</p>

                    <div class="rp-field">
                        <label class="rp-label">Parent Product</label>
                        <select v-model="parentId" class="rp-select">
                            <option :value="null">Select parent…</option>
                            <option v-for="p in productsData" :key="p.id" :value="p.id">{{ p.name }}</option>
                        </select>
                    </div>

                    <div class="rp-field">
                        <label class="rp-label">Child Product</label>
                        <select v-model="childId" class="rp-select">
                            <option :value="null">Select child…</option>
                            <option v-for="p in childOptions" :key="p.id" :value="p.id">
                                {{ p.name }}<template v-if="p.parent_id"> (has parent)</template>
                            </option>
                        </select>
                    </div>

                    <button @click="assignRelationship" :disabled="!parentId || !childId || submitting"
                        class="rp-assign-btn">
                        <svg v-if="submitting" class="rp-spinner" viewBox="0 0 24 24" fill="none">
                            <circle cx="12" cy="12" r="10" stroke="rgba(255,255,255,0.3)" stroke-width="3" />
                            <path d="M12 2a10 10 0 0 1 10 10" stroke="#fff" stroke-width="3" stroke-linecap="round" />
                        </svg>
                        {{ submitting ? 'Saving…' : 'Assign Relationship' }}
                    </button>
                </section>

                <!-- Legend -->
                <section class="rp-card">
                    <h2 class="rp-card-title">Legend</h2>
                    <div class="rp-legend">
                        <div class="rp-legend-row">
                            <span class="rp-dot rp-dot--blush"></span>
                            <span>Root / Parent product</span>
                        </div>
                        <div class="rp-legend-row">
                            <span class="rp-dot rp-dot--lav"></span>
                            <span>Child product (variant)</span>
                        </div>
                        <div class="rp-legend-row">
                            <span class="rp-dot rp-dot--warn"></span>
                            <span>Orphaned (parent missing)</span>
                        </div>
                        <div class="rp-legend-tips">
                            <p>▶ / ▼ — expand / collapse children</p>
                            <p>Click name to edit product</p>
                            <p>✕ — remove from parent</p>
                        </div>
                    </div>
                </section>

            </div>

            <!-- Tree panel -->
            <div class="rp-tree-panel">
                <div class="rp-card rp-card--flush">

                    <!-- Search -->
                    <div class="rp-search">
                        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                            stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="rp-search-icon">
                            <circle cx="11" cy="11" r="8" />
                            <path d="m21 21-4.3-4.3" />
                        </svg>
                        <input v-model="searchQuery" type="text" placeholder="Search products…"
                            class="rp-search-input" />
                        <button v-if="searchQuery" @click="searchQuery = ''" class="rp-search-clear" aria-label="Clear">
                            <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                stroke-width="2.5" stroke-linecap="round">
                                <path d="M18 6 6 18M6 6l12 12" />
                            </svg>
                        </button>
                    </div>

                    <!-- Tree -->
                    <div class="rp-tree-scroll">
                        <p v-if="filteredTree.length === 0" class="rp-tree-empty">
                            {{ searchQuery ? 'No products match your search.' : 'No products found.' }}
                        </p>
                        <div v-for="root in filteredTree" :key="root.id">
                            <TreeNodeRow :node="root" :depth="0" :collapsed-map="collapsed" @toggle="toggleCollapse"
                                @edit="id => router.get(route('admin.products.edit', id))"
                                @remove="removeRelationship" />
                        </div>
                    </div>

                </div>
            </div>

        </div>
    </AdminLayout>
</template>

<script lang="ts">
import { defineComponent, h, PropType } from 'vue';

interface TreeNodeDef {
    id: number;
    name: string;
    parent_id: number | null;
    mpn?: string;
    status?: string;
    stock_qty?: number;
    children: TreeNodeDef[];
}

const TreeNodeRow = defineComponent({
    name: 'TreeNodeRow',
    props: {
        node: { type: Object as PropType<TreeNodeDef>, required: true },
        depth: { type: Number, default: 0 },
        collapsedMap: { type: Object as PropType<Record<number, boolean>>, required: true },
    },
    emits: ['toggle', 'edit', 'remove'],
    setup(props, { emit }) {
        return () => {
            const node = props.node;
            const isRoot = !node.parent_id;
            const hasChildren = node.children.length > 0;
            const isCollapsed = props.collapsedMap[node.id];
            const indent = props.depth * 20;

            // ── Row ──
            const row = h('div', {
                class: 'rp-tree-row',
                style: { paddingLeft: `${indent + 10}px` },
            }, [
                // Toggle arrow
                h('button', {
                    class: 'rp-tree-toggle',
                    style: !hasChildren ? { opacity: '0', pointerEvents: 'none' } : {},
                    onClick: () => hasChildren && emit('toggle', node.id),
                }, isCollapsed ? '▶' : '▼'),

                // Colour dot
                h('span', {
                    class: [
                        'rp-tree-dot',
                        isRoot ? 'rp-tree-dot--root' : 'rp-tree-dot--child',
                    ],
                }),

                // Name
                h('button', {
                    class: ['rp-tree-name', isRoot ? 'rp-tree-name--root' : ''],
                    onClick: () => emit('edit', node.id),
                    title: `Edit: ${node.name}`,
                }, node.name),

                // MPN chip
                node.mpn && h('span', { class: 'rp-tree-mpn' }, node.mpn),

                // Children count pill
                hasChildren && h('span', { class: 'rp-tree-count' }, node.children.length),

                // Status dot
                node.status && h('span', {
                    class: [
                        'rp-tree-status',
                        node.status === 'enabled' ? 'rp-tree-status--on' : 'rp-tree-status--off',
                    ],
                }),

                // Remove button (children only, revealed on row hover)
                !isRoot && h('button', {
                    class: 'rp-tree-remove',
                    title: 'Remove from parent',
                    onClick: (e: Event) => { e.stopPropagation(); emit('remove', node.id); },
                }, '✕'),
            ]);

            // ── Children ──
            const children = (!isCollapsed && hasChildren)
                ? h('div', { class: 'rp-tree-children' }, [
                    h('div', {
                        class: 'rp-tree-connector',
                        style: { left: `${indent + 20}px` },
                    }),
                    ...node.children.map(child =>
                        h(TreeNodeRow, {
                            key: child.id,
                            node: child,
                            depth: props.depth + 1,
                            collapsedMap: props.collapsedMap,
                            onToggle: (id: number) => emit('toggle', id),
                            onEdit: (id: number) => emit('edit', id),
                            onRemove: (id: number) => emit('remove', id),
                        })
                    ),
                ])
                : null;

            return h('div', {}, [row, children]);
        };
    },
});

export default { components: { TreeNodeRow } };
</script>

<style scoped>
/* ── Tokens ── */
.rp-header,
.rp-stats,
.rp-grid {
    --bb-navy: #1a1a2e;
    --bb-cream: #faf9f7;
    --bb-surface: #ffffff;
    --bb-border: #ece8e2;
    --bb-text: #1a1a2e;
    --bb-muted: #7a7a9a;
    --bb-red: #e05c6e;
    --bb-green: #4caf7d;
    --bb-blush: #f2c4ce;
    --bb-blush-d: #d4899a;
    --bb-lav: #c9b8f0;
    --bb-lav-d: #9b84d4;
    --bb-sage: #b8d9b8;
    --bb-sage-d: #4caf7d;
    --bb-warn-d: #c8820a;
    font-family: 'DM Sans', sans-serif;
}

/* ── Header ── */
.rp-header {
    margin-bottom: 1.5rem;
}

.rp-breadcrumb {
    display: flex;
    align-items: center;
    gap: 0.4rem;
    font-size: 0.78rem;
    color: var(--bb-muted);
    margin-bottom: 0.35rem;
}

.rp-breadcrumb-link {
    color: var(--bb-muted);
    text-decoration: none;
    transition: color 0.15s;
}

.rp-breadcrumb-link:hover {
    color: var(--bb-text);
}

.rp-breadcrumb-sep {
    opacity: 0.5;
}

.rp-title {
    font-size: 1.5rem;
    font-weight: 700;
    letter-spacing: -0.02em;
    color: var(--bb-text);
}

.rp-sub {
    font-size: 0.82rem;
    color: var(--bb-muted);
    margin-top: 0.2rem;
}

/* ── Flash ── */
.rp-flash {
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    padding: 0.6rem 1rem;
    border-radius: 8px;
    margin-bottom: 1.25rem;
    background: #eef7f2;
    border: 1px solid #b8dfc8;
    color: #2a7a50;
    font-size: 0.85rem;
    font-weight: 600;
}

.rp-fade-enter-active,
.rp-fade-leave-active {
    transition: opacity 0.3s, transform 0.3s;
}

.rp-fade-enter-from,
.rp-fade-leave-to {
    opacity: 0;
    transform: translateY(-6px);
}

/* ── Stats ── */
.rp-stats {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 0.85rem;
    margin-bottom: 1.5rem;
}

@media (max-width: 640px) {
    .rp-stats {
        grid-template-columns: repeat(2, 1fr);
    }
}

.rp-stat {
    background: var(--bb-surface);
    border-radius: 12px;
    border: 1px solid var(--bb-border);
    padding: 1rem 0.85rem;
    text-align: center;
    box-shadow: 0 1px 4px rgba(26, 26, 46, 0.04);
}

.rp-stat--blush {
    background: #fff5f7;
    border-color: var(--bb-blush);
}

.rp-stat--lav {
    background: #f8f6ff;
    border-color: var(--bb-lav);
}

.rp-stat--sage {
    background: #f2faf2;
    border-color: var(--bb-sage);
}

.rp-stat--warn {
    background: #fffbf0;
    border-color: #f5d5a0;
}

.rp-stat-val {
    font-size: 1.6rem;
    font-weight: 700;
    color: var(--bb-text);
    line-height: 1.1;
    letter-spacing: -0.02em;
}

.rp-stat--blush .rp-stat-val {
    color: var(--bb-blush-d);
}

.rp-stat--lav .rp-stat-val {
    color: var(--bb-lav-d);
}

.rp-stat--sage .rp-stat-val {
    color: var(--bb-sage-d);
}

.rp-stat--warn .rp-stat-val {
    color: var(--bb-warn-d);
}

.rp-stat-label {
    font-size: 0.72rem;
    font-weight: 600;
    color: var(--bb-muted);
    margin-top: 0.2rem;
    letter-spacing: 0.04em;
}

/* ── Main grid ── */
.rp-grid {
    display: grid;
    grid-template-columns: 260px 1fr;
    gap: 1.25rem;
    align-items: start;
}

@media (max-width: 1024px) {
    .rp-grid {
        grid-template-columns: 1fr;
    }
}

.rp-sidebar {
    display: flex;
    flex-direction: column;
    gap: 1.25rem;
}

/* ── Cards ── */
.rp-card {
    background: var(--bb-surface);
    border-radius: 14px;
    border: 1px solid var(--bb-border);
    box-shadow: 0 1px 6px rgba(26, 26, 46, 0.05);
    padding: 1.25rem;
    display: flex;
    flex-direction: column;
    gap: 0.85rem;
}

.rp-card--flush {
    padding: 0;
    gap: 0;
    overflow: hidden;
}

.rp-card-title {
    font-size: 0.78rem;
    font-weight: 700;
    letter-spacing: 0.08em;
    text-transform: uppercase;
    color: var(--bb-muted);
    padding-bottom: 0.75rem;
    border-bottom: 1px solid var(--bb-border);
}

.rp-card-hint {
    font-size: 0.8rem;
    color: var(--bb-muted);
    font-style: italic;
    margin-top: -0.25rem;
}

/* ── Assign form ── */
.rp-field {
    display: flex;
    flex-direction: column;
    gap: 0.3rem;
}

.rp-label {
    font-size: 0.7rem;
    font-weight: 700;
    letter-spacing: 0.07em;
    text-transform: uppercase;
    color: var(--bb-muted);
}

.rp-select {
    width: 100%;
    padding: 0.6rem 0.8rem;
    border-radius: 8px;
    border: 1px solid var(--bb-border);
    background: var(--bb-cream);
    color: var(--bb-text);
    font-family: 'DM Sans', sans-serif;
    font-size: 0.85rem;
    outline: none;
    transition: border-color 0.15s, box-shadow 0.15s;
}

.rp-select:focus {
    border-color: var(--bb-lav-d);
    box-shadow: 0 0 0 3px rgba(201, 184, 240, 0.2);
}

.rp-assign-btn {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 0.45rem;
    width: 100%;
    padding: 0.65rem;
    border-radius: 8px;
    border: none;
    background: var(--bb-navy);
    color: #fff;
    font-family: 'DM Sans', sans-serif;
    font-size: 0.85rem;
    font-weight: 600;
    cursor: pointer;
    transition: opacity 0.15s, transform 0.15s;
}

.rp-assign-btn:hover:not(:disabled) {
    opacity: 0.88;
    transform: translateY(-1px);
}

.rp-assign-btn:disabled {
    opacity: 0.4;
    cursor: not-allowed;
}

.rp-spinner {
    width: 14px;
    height: 14px;
    animation: rp-spin 0.8s linear infinite;
}

@keyframes rp-spin {
    from {
        transform: rotate(0deg);
    }

    to {
        transform: rotate(360deg);
    }
}

/* ── Legend ── */
.rp-legend {
    display: flex;
    flex-direction: column;
    gap: 0.5rem;
}

.rp-legend-row {
    display: flex;
    align-items: center;
    gap: 0.6rem;
    font-size: 0.82rem;
    color: var(--bb-muted);
}

.rp-dot {
    width: 10px;
    height: 10px;
    border-radius: 50%;
    flex-shrink: 0;
}

.rp-dot--blush {
    background: var(--bb-blush-d);
}

.rp-dot--lav {
    background: var(--bb-lav-d);
}

.rp-dot--warn {
    background: var(--bb-warn-d);
}

.rp-legend-tips {
    border-top: 1px solid var(--bb-border);
    padding-top: 0.5rem;
    margin-top: 0.1rem;
    display: flex;
    flex-direction: column;
    gap: 0.18rem;
    font-size: 0.72rem;
    color: var(--bb-muted);
}

/* ── Search ── */
.rp-search {
    display: flex;
    align-items: center;
    gap: 0.6rem;
    padding: 0.75rem 1rem;
    border-bottom: 1px solid var(--bb-border);
    background: var(--bb-cream);
}

.rp-search-icon {
    color: var(--bb-muted);
    flex-shrink: 0;
}

.rp-search-input {
    flex: 1;
    background: transparent;
    border: none;
    outline: none;
    font-family: 'DM Sans', sans-serif;
    font-size: 0.88rem;
    color: var(--bb-text);
}

.rp-search-input::placeholder {
    color: var(--bb-muted);
}

.rp-search-clear {
    width: 22px;
    height: 22px;
    border-radius: 50%;
    border: none;
    background: none;
    color: var(--bb-muted);
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    transition: background 0.12s, color 0.12s;
}

.rp-search-clear:hover {
    background: var(--bb-border);
    color: var(--bb-text);
}

/* ── Tree ── */
.rp-tree-scroll {
    padding: 1rem;
    overflow-y: auto;
    max-height: 70vh;
}

.rp-tree-empty {
    font-size: 0.85rem;
    color: var(--bb-muted);
    font-style: italic;
    text-align: center;
    padding: 3rem 1rem;
}
</style>

<!-- Tree node styles must be unscoped so the recursive render function can reach them -->
<style>
.rp-tree-row {
    display: flex;
    align-items: center;
    gap: 0.45rem;
    padding: 0.42rem 0.75rem;
    border-radius: 8px;
    margin-bottom: 1px;
    transition: background 0.12s;
    font-family: 'DM Sans', sans-serif;
}

.rp-tree-row:hover {
    background: #faf9f7;
}

.rp-tree-toggle {
    font-size: 0.52rem;
    color: #7a7a9a;
    background: none;
    border: none;
    width: 14px;
    flex-shrink: 0;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    transition: color 0.12s;
}

.rp-tree-toggle:hover {
    color: #1a1a2e;
}

.rp-tree-dot {
    width: 8px;
    height: 8px;
    border-radius: 50%;
    flex-shrink: 0;
}

.rp-tree-dot--root {
    background: #d4899a;
}

.rp-tree-dot--child {
    background: #9b84d4;
}

.rp-tree-name {
    flex: 1;
    min-width: 0;
    text-align: left;
    background: none;
    border: none;
    padding: 0;
    font-family: 'DM Sans', sans-serif;
    font-size: 0.88rem;
    font-weight: 500;
    color: #1a1a2e;
    cursor: pointer;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
    transition: color 0.12s;
}

.rp-tree-name:hover {
    color: #9b84d4;
}

.rp-tree-name--root {
    font-weight: 600;
}

.rp-tree-mpn {
    font-size: 0.62rem;
    font-family: monospace;
    color: #7a7a9a;
    white-space: nowrap;
    flex-shrink: 0;
}

.rp-tree-count {
    font-size: 0.62rem;
    font-weight: 700;
    padding: 0.08rem 0.4rem;
    border-radius: 999px;
    background: #f0edf8;
    color: #9b84d4;
    flex-shrink: 0;
}

.rp-tree-status {
    width: 7px;
    height: 7px;
    border-radius: 50%;
    flex-shrink: 0;
}

.rp-tree-status--on {
    background: #4caf7d;
}

.rp-tree-status--off {
    background: #7a7a9a;
}

.rp-tree-remove {
    font-size: 0.68rem;
    background: none;
    border: none;
    color: #7a7a9a;
    cursor: pointer;
    padding: 0 0.15rem;
    flex-shrink: 0;
    opacity: 0;
    transition: opacity 0.12s, color 0.12s;
}

.rp-tree-row:hover .rp-tree-remove {
    opacity: 1;
}

.rp-tree-remove:hover {
    color: #e05c6e;
}

.rp-tree-children {
    position: relative;
}

.rp-tree-connector {
    position: absolute;
    top: 0;
    bottom: 8px;
    width: 1px;
    background: #ece8e2;
}
</style>
