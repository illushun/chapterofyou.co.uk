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
    collapsed: ref<boolean>;
}

const props = defineProps<{
    productsData: ProductNode[];
}>();

// ── Form state ─────────────────────────────────────────────────────────────
const parentId = ref<number | null>(null);
const childId = ref<number | null>(null);
const submitting = ref(false);
const searchQuery = ref('');
const flash = ref<string | null>(null);

// ── Build tree ─────────────────────────────────────────────────────────────
const collapsed = ref<Record<number, boolean>>({});

const toggleCollapse = (id: number) => {
    collapsed.value[id] = !collapsed.value[id];
};

const buildTree = (items: ProductNode[]): TreeNode[] => {
    const map: Record<number, TreeNode> = {};
    const roots: TreeNode[] = [];

    items.forEach(item => {
        map[item.id] = { ...item, children: [], collapsed: false } as any;
    });

    items.forEach(item => {
        if (item.parent_id && map[item.parent_id]) {
            map[item.parent_id].children.push(map[item.id]);
        } else if (!item.parent_id) {
            roots.push(map[item.id]);
        }
    });

    return roots;
};

const filteredTree = computed(() => {
    if (!searchQuery.value.trim()) return buildTree(props.productsData);
    const q = searchQuery.value.toLowerCase();
    const matchingIds = new Set<number>();
    const ancestorIds = new Set<number>();

    // Find matching products
    props.productsData.forEach(p => {
        if (p.name.toLowerCase().includes(q) || String(p.id).includes(q)) {
            matchingIds.add(p.id);
            // Include ancestors
            let current = p;
            while (current.parent_id) {
                ancestorIds.add(current.parent_id);
                const parent = props.productsData.find(x => x.id === current.parent_id);
                if (!parent) break;
                current = parent;
            }
        }
    });

    const filtered = props.productsData.filter(p =>
        matchingIds.has(p.id) || ancestorIds.has(p.id)
    );
    return buildTree(filtered);
});

// Stats
const totalProducts = computed(() => props.productsData.length);
const parentProducts = computed(() => props.productsData.filter(p => !p.parent_id).length);
const childProducts = computed(() => props.productsData.filter(p => p.parent_id).length);
const orphans = computed(() => {
    const parentIds = new Set(props.productsData.filter(p => !p.parent_id).map(p => p.id));
    return props.productsData.filter(p => p.parent_id && !parentIds.has(p.parent_id)).length;
});

// Child options (exclude self and current parent's ancestors to prevent cycles)
const childOptions = computed(() =>
    props.productsData.filter(p => p.id !== parentId.value)
);

function assignRelationship() {
    if (!parentId.value || !childId.value) return;
    submitting.value = true;
    router.post(route('admin.products.assign-relationship'), {
        parent_id: parentId.value,
        child_id: childId.value,
    }, {
        preserveScroll: true,
        onSuccess: () => {
            flash.value = 'Relationship assigned successfully.';
            parentId.value = null;
            childId.value = null;
            setTimeout(() => { flash.value = null; }, 3000);
        },
        onFinish: () => { submitting.value = false; },
    });
}

function removeRelationship(productId: number) {
    router.post(route('admin.products.remove-relationship'), {
        product_id: productId,
    }, { preserveScroll: true });
}
</script>

<template>
    <AdminLayout>

        <Head title="Product Relationships" />

        <!-- Header -->
        <div class="mb-6 flex flex-wrap items-center justify-between gap-3 border-b-2 border-copy pb-2">
            <div>
                <h2 class="text-3xl font-black">Product Relationships</h2>
                <p class="text-copy-light text-sm mt-0.5">Manage parent–child product hierarchies</p>
            </div>
            <Link :href="route('admin.products.index')"
                class="text-sm font-medium text-copy-light hover:text-copy transition">
            ← Back to Products
            </Link>
        </div>

        <!-- Flash -->
        <div v-if="flash"
            class="mb-4 rounded-lg border border-green-300 bg-green-50 px-4 py-3 text-sm font-medium text-green-800">
            ✓ {{ flash }}
        </div>

        <!-- Stats row -->
        <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 mb-6">
            <div class="rounded-xl border-2 border-copy bg-[var(--primary-content)]">
                <div class="relative rounded-xl -m-0.5 border-2 border-copy bg-foreground p-4 text-center">
                    <p class="text-2xl font-black text-copy">{{ totalProducts }}</p>
                    <p class="text-xs text-copy-light mt-0.5">Total Products</p>
                </div>
            </div>
            <div class="rounded-xl border-2 border-copy bg-[var(--primary-content)]">
                <div class="relative rounded-xl -m-0.5 border-2 border-copy bg-foreground p-4 text-center">
                    <p class="text-2xl font-black text-primary">{{ parentProducts }}</p>
                    <p class="text-xs text-copy-light mt-0.5">Parent Products</p>
                </div>
            </div>
            <div class="rounded-xl border-2 border-copy bg-[var(--primary-content)]">
                <div class="relative rounded-xl -m-0.5 border-2 border-copy bg-foreground p-4 text-center">
                    <p class="text-2xl font-black text-copy">{{ childProducts }}</p>
                    <p class="text-xs text-copy-light mt-0.5">Child Products</p>
                </div>
            </div>
            <div class="rounded-xl border-2 border-copy bg-[var(--primary-content)]">
                <div class="relative rounded-xl -m-0.5 border-2 border-copy bg-foreground p-4 text-center">
                    <p class="text-2xl font-black" :class="orphans > 0 ? 'text-amber-600' : 'text-green-600'">
                        {{ orphans }}
                    </p>
                    <p class="text-xs text-copy-light mt-0.5">Orphaned</p>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

            <!-- ── Left panel: assign + legend ── -->
            <div class="space-y-5">

                <!-- Assign relationship -->
                <div class="rounded-xl border-2 border-copy bg-[var(--primary-content)]">
                    <div class="relative rounded-xl -m-0.5 border-2 border-copy bg-foreground p-5">
                        <h3 class="text-base font-bold text-copy mb-1">Assign Relationship</h3>
                        <p class="text-xs text-copy-light mb-4">Link a child product to a parent product.</p>

                        <div class="space-y-3">
                            <div>
                                <label class="block text-xs font-medium text-copy-light mb-1 uppercase tracking-wider">
                                    Parent Product
                                </label>
                                <select v-model="parentId"
                                    class="w-full rounded-lg border-2 border-copy bg-foreground px-3 py-2 text-sm text-copy focus:outline-none">
                                    <option :value="null">— Select parent —</option>
                                    <option v-for="p in productsData" :key="p.id" :value="p.id">
                                        {{ p.name }}
                                    </option>
                                </select>
                            </div>

                            <div>
                                <label class="block text-xs font-medium text-copy-light mb-1 uppercase tracking-wider">
                                    Child Product
                                </label>
                                <select v-model="childId"
                                    class="w-full rounded-lg border-2 border-copy bg-foreground px-3 py-2 text-sm text-copy focus:outline-none">
                                    <option :value="null">— Select child —</option>
                                    <option v-for="p in childOptions" :key="p.id" :value="p.id">
                                        {{ p.name }}
                                        <template v-if="p.parent_id"> (currently child)</template>
                                    </option>
                                </select>
                            </div>

                            <button @click="assignRelationship" :disabled="!parentId || !childId || submitting"
                                class="w-full rounded-lg border-2 border-copy py-2 text-sm font-bold transition disabled:opacity-40"
                                style="background-color: var(--primary); color: var(--primary-content);">
                                {{ submitting ? 'Saving…' : 'Assign Relationship' }}
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Legend -->
                <div class="rounded-xl border-2 border-copy bg-[var(--primary-content)]">
                    <div class="relative rounded-xl -m-0.5 border-2 border-copy bg-foreground p-5">
                        <h3 class="text-base font-bold text-copy mb-3">Legend</h3>
                        <div class="space-y-2 text-sm">
                            <div class="flex items-center gap-2">
                                <span class="w-3 h-3 rounded-full bg-primary flex-shrink-0"></span>
                                <span class="text-copy-light">Root / Parent product</span>
                            </div>
                            <div class="flex items-center gap-2">
                                <span
                                    class="w-3 h-3 rounded-full bg-secondary-light border border-copy flex-shrink-0"></span>
                                <span class="text-copy-light">Child product (variant)</span>
                            </div>
                            <div class="flex items-center gap-2">
                                <span class="w-3 h-3 rounded-full bg-amber-400 flex-shrink-0"></span>
                                <span class="text-copy-light">Orphaned (parent missing)</span>
                            </div>
                            <div class="pt-2 border-t border-copy-light text-xs text-copy-light space-y-1">
                                <p>▶ / ▼ — expand / collapse children</p>
                                <p>Click product name to edit</p>
                                <p>✕ — remove from parent</p>
                            </div>
                        </div>
                    </div>
                </div>

            </div>

            <!-- ── Right panel: tree ── -->
            <div class="lg:col-span-2">
                <div class="rounded-xl border-2 border-copy bg-[var(--primary-content)]">
                    <div class="relative rounded-xl -m-0.5 border-2 border-copy bg-foreground overflow-hidden">

                        <!-- Search bar -->
                        <div class="px-5 py-3 border-b border-copy-light flex items-center gap-3">
                            <svg class="w-4 h-4 text-copy-light flex-shrink-0" fill="none" viewBox="0 0 24 24"
                                stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                            </svg>
                            <input v-model="searchQuery" type="text" placeholder="Search products…"
                                class="flex-1 bg-transparent text-sm text-copy placeholder-copy-light focus:outline-none" />
                            <button v-if="searchQuery" @click="searchQuery = ''"
                                class="text-xs text-copy-light hover:text-copy">✕</button>
                        </div>

                        <!-- Tree -->
                        <div class="p-5 overflow-auto max-h-[70vh]">
                            <div v-if="filteredTree.length === 0"
                                class="text-center py-12 text-copy-light italic text-sm">
                                {{ searchQuery ? 'No products match your search.' : 'No products found.' }}
                            </div>

                            <!-- Recursive tree component -->
                            <div v-for="root in filteredTree" :key="root.id" class="mb-2">
                                <TreeNodeRow :node="root" :depth="0" :collapsed-map="collapsed" @toggle="toggleCollapse"
                                    @edit="id => router.get(route('admin.products.edit', id))"
                                    @remove="removeRelationship" />
                            </div>
                        </div>

                    </div>
                </div>
            </div>

        </div>
    </AdminLayout>
</template>

<!-- Recursive sub-component defined in the same file -->
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

            return h('div', { class: 'select-none' }, [
                // Row
                h('div', {
                    class: [
                        'flex items-center gap-2 px-3 py-2 rounded-lg mb-0.5 group transition',
                        'hover:bg-secondary-light',
                        props.depth === 0 ? 'border border-copy-light/50' : '',
                    ],
                    style: { marginLeft: `${indent}px` },
                }, [
                    // Expand/collapse toggle
                    h('button', {
                        class: 'w-5 h-5 flex items-center justify-center text-xs text-copy-light flex-shrink-0',
                        onClick: () => hasChildren && emit('toggle', node.id),
                    }, hasChildren ? (isCollapsed ? '▶' : '▼') : '·'),

                    // Colour dot
                    h('span', {
                        class: [
                            'w-2.5 h-2.5 rounded-full flex-shrink-0',
                            isRoot ? 'bg-[var(--primary)]'
                                : node.children.length > 0 ? 'bg-blue-400'
                                    : 'bg-secondary-light border border-copy',
                        ],
                    }),

                    // Product name (clickable to edit)
                    h('button', {
                        class: [
                            'text-sm font-medium text-left flex-1 truncate',
                            isRoot ? 'font-bold text-copy' : 'text-copy',
                            'hover:text-primary transition',
                        ],
                        onClick: () => emit('edit', node.id),
                        title: `Edit: ${node.name}`,
                    }, node.name),

                    // Child count badge
                    hasChildren && h('span', {
                        class: 'text-xs text-copy-light bg-secondary-light px-1.5 py-0.5 rounded-full flex-shrink-0',
                    }, `${node.children.length}`),

                    // Status badge
                    node.status && h('span', {
                        class: [
                            'text-xs px-1.5 py-0.5 rounded-full flex-shrink-0',
                            node.status === 'enabled' ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-600',
                        ],
                    }, node.status === 'enabled' ? '●' : '○'),

                    // Remove from parent (only for child products)
                    !isRoot && h('button', {
                        class: 'opacity-0 group-hover:opacity-100 text-xs text-copy-light hover:text-error transition flex-shrink-0',
                        title: 'Remove from parent',
                        onClick: (e: Event) => { e.stopPropagation(); emit('remove', node.id); },
                    }, '✕'),
                ]),

                // Connector line + children
                !isCollapsed && hasChildren && h('div', {
                    class: 'relative',
                    style: { marginLeft: `${indent + 10}px` },
                }, [
                    // Vertical line
                    h('div', {
                        class: 'absolute left-0 top-0 bottom-2 w-px bg-copy-light/30',
                        style: { marginLeft: '12px' },
                    }),
                    // Recurse
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
                ]),
            ]);
        };
    },
});

export default { components: { TreeNodeRow } };
</script>
