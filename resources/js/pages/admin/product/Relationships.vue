<script setup lang="ts">
import AdminLayout from '@/layouts/AdminLayout.vue';
import { Head, router } from '@inertiajs/vue3';
import { computed, onMounted, ref } from 'vue';
import * as d3 from 'd3';

// 1. Typescript Declaration for route helper
import type { Route } from 'ziggy-js';
declare const route: Route;

// 2. Define Props
interface ProductNode {
    id: number;
    name: string;
    // Matches the property name used in the AdminProductController's relationshipIndex() map
    parent_product_id: number | null;
}

const props = defineProps<{
    productsData: ProductNode[];
}>();

// 3. State Management
const graphContainer = ref<HTMLElement | null>(null);
const isLoading = ref(true);

// New state for form inputs
const parentProductId = ref<number | null>(null);
const childProductId = ref<number | null>(null);

// D3 Node calculation
const nodes = computed(() => {
    return props.productsData.map(p => ({
        ...p,
        // D3 requires mutable x/y positions
        x: 0,
        y: 0,
        // Determine if this is a root product
        is_root: p.parent_product_id === null,
        group: p.parent_product_id === null ? 1 : 2, // 1: Root (Blue), 2: Child (Orange)
        parent_id: p.parent_product_id // D3 Link function relies on this being the parent ID
    }));
});

// D3 Link calculation (Parent-Child relationships)
const links = computed(() => {
    // 1. Get the raw links (source ID, target ID)
    const linkSet = props.productsData
        .filter(p => p.parent_product_id !== null)
        .map(p => ({
            source: p.parent_product_id,
            target: p.id,
        }));

    // 2. Map IDs to node objects for D3 simulation
    const nodeMap = new Map(nodes.value.map(n => [n.id, n]));

    return linkSet.map(link => ({
        source: nodeMap.get(link.source as number),
        target: nodeMap.get(link.target),
    })).filter(link => link.source && link.target);
});

// 4. Form Submission Logic
const assignRelationship = () => {
    // Use a basic check/log since we can't use alert() or confirm()
    if (parentProductId.value === null || childProductId.value === null) {
        console.error("Please select both a Parent and a Child product.");
        return;
    }

    if (parentProductId.value === childProductId.value) {
        console.error("A product cannot be its own parent.");
        return;
    }

    // This is the Inertia POST request to update the relationship
    // NOTE: Replace 'admin.products.assign-relationship' with your actual route name
    router.post(route('admin.products.assign-relationship'), {
        parent_id: parentProductId.value,
        child_id: childProductId.value,
    }, {
        // Clear the form and trigger re-render on success
        onSuccess: () => {
            parentProductId.value = null;
            childProductId.value = null;
            // The Inertia page reload will automatically re-run the D3 graph initialization
        },
        onError: (errors) => {
            console.error("Error assigning relationship:", errors);
        }
    });
};


// 5. D3 Visualization Logic
const initializeGraph = () => {
    if (!graphContainer.value) return;

    isLoading.value = true;

    const container = graphContainer.value;

    // Clear any previous SVG instance
    d3.select(container).selectAll("svg").remove();

    // Use container size for responsiveness
    const width = container.clientWidth;
    const height = Math.max(container.clientHeight, 600); // Set minimum height

    const svg = d3.select(container)
        .append("svg")
        .attr("viewBox", `0 0 ${width} ${height}`)
        .style("width", "100%")
        .style("height", "100%")
        .attr("class", "bg-foreground rounded-xl"); // Styling moved to container class

    // Group element for transformation (pan/zoom)
    const g = svg.append("g");

    // Pan and zoom behavior
    const zoom = d3.zoom()
        .scaleExtent([0.1, 4])
        .on("zoom", (event) => {
            g.attr("transform", event.transform);
        });

    svg.call(zoom as any);

    // D3 Force Simulation setup
    const simulation = d3.forceSimulation(nodes.value as d3.SimulationNodeDatum[])
        .force("link", d3.forceLink(links.value as d3.SimulationLinkDatum<d3.SimulationNodeDatum>[])
            .id((d: any) => d.id)
            .distance(150) // Distance between nodes
        )
        .force("charge", d3.forceManyBody().strength(-300)) // Repel nodes
        .force("center", d3.forceCenter(width / 2, height / 2))
        .on("tick", ticked);

    // Color scale
    // Group 1 is blue (Root), Group 2 is orange (Child)
    const color = d3.scaleOrdinal(d3.schemeCategory10);

    // 5.1. Links (Lines)
    const link = g.append("g")
        .attr("stroke", "currentColor")
        .attr("stroke-opacity", 0.6)
        .attr("class", "text-copy-light")
        .selectAll("line")
        .data(links.value)
        .join("line")
        .attr("stroke-width", 2);

    // 5.2. Nodes (Circles)
    const node = g.append("g")
        .attr("stroke", "#fff")
        .attr("stroke-width", 1.5)
        .selectAll("circle")
        .data(nodes.value)
        .join("circle")
        .attr("r", (d: any) => d.is_root ? 12 : 8) // Larger circle for root products
        .attr("fill", (d: any) => color(d.group))
        .attr("class", "cursor-pointer transition-all duration-150 ease-in-out hover:stroke-4")
        .on("click", (event, d: any) => {
            // Navigate to product edit page on click
            router.get(route('admin.products.edit', d.id));
        })
        .call(d3.drag<any, any>()
            .on("start", dragstarted)
            .on("drag", dragged)
            .on("end", dragended));

    // 5.3. Labels (Text)
    const label = g.append("g")
        .attr("class", "text-sm font-semibold text-copy pointer-events-none select-none")
        .selectAll("text")
        .data(nodes.value)
        .join("text")
        .attr("x", (d: any) => d.is_root ? 18 : 12) // Offset based on circle size
        .attr("y", 5)
        .text((d: any) => d.name)
        .attr("font-family", "Inter, sans-serif")
        .attr("font-size", 12);

    // 5.4. Tick Function (Updates position on each simulation tick)
    function ticked() {
        link
            .attr("x1", (d: any) => d.source.x)
            .attr("y1", (d: any) => d.source.y)
            .attr("x2", (d: any) => d.target.x)
            .attr("y2", (d: any) => d.target.y);

        node
            .attr("cx", (d: any) => d.x)
            .attr("cy", (d: any) => d.y);

        label
            .attr("transform", (d: any) => `translate(${d.x}, ${d.y})`);
    }

    // 5.5. Drag Handlers
    function dragstarted(event: any) {
        if (!event.active) simulation.alphaTarget(0.3).restart();
        event.subject.fx = event.subject.x;
        event.subject.fy = event.subject.y;
    }

    function dragged(event: any) {
        event.subject.fx = event.x;
        event.subject.fy = event.y;
    }

    function dragended(event: any) {
        if (!event.active) simulation.alphaTarget(0);
        event.subject.fx = null;
        event.subject.fy = null;
    }

    // Set loading false after initial rendering
    setTimeout(() => isLoading.value = false, 500);
};

// 6. Lifecycle Hooks
onMounted(() => {
    initializeGraph();
    // Re-initialize graph on window resize to make it responsive
    window.addEventListener('resize', initializeGraph);
});

</script>

<template>
    <AdminLayout>

        <Head title="Product Relationships" />

        <!-- Consistent Header Style -->
        <div class="flex justify-between items-center mb-6 border-b-2 border-copy pb-2">
            <h2 class="text-3xl font-black">Product Relationship Visualizer</h2>
            <Link :href="route('admin.products.index')"
                class="text-sm font-semibold text-blue-500 hover:text-blue-700 transition">
            &larr; Back to Products
            </Link>
        </div>

        <!-- Controls and Visualization Grid -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

            <!-- Mass Assignment Panel (Styled as a Card) -->
            <div class="lg:col-span-1 p-6 rounded-lg border-2 border-copy bg-foreground shadow-lg h-full">
                <h2 class="text-xl font-bold mb-4 text-copy">Assign Relationship</h2>
                <p class="text-copy-light mb-6 text-sm">
                    Select two products to establish a parent-child relationship.
                </p>

                <form @submit.prevent="assignRelationship" class="space-y-4">
                    <!-- Parent Product Select -->
                    <div>
                        <label for="parent_select" class="block text-sm font-medium text-copy mb-1">Parent
                            Product</label>
                        <select id="parent_select" v-model="parentProductId"
                            class="w-full px-3 py-2 border border-copy-light/50 rounded-lg bg-secondary-light text-copy focus:ring-primary focus:border-primary transition">
                            <option :value="null" disabled>Select Parent Product</option>
                            <option v-for="p in props.productsData" :key="p.id" :value="p.id">{{ p.name }} (ID: {{ p.id
                                }})</option>
                        </select>
                    </div>

                    <!-- Child Product Select -->
                    <div>
                        <label for="child_select" class="block text-sm font-medium text-copy mb-1">Child Product</label>
                        <select id="child_select" v-model="childProductId"
                            class="w-full px-3 py-2 border border-copy-light/50 rounded-lg bg-secondary-light text-copy focus:ring-primary focus:border-primary transition">
                            <option :value="null" disabled>Select Child Product</option>
                            <!-- Prevent a product from being a child of itself -->
                            <option v-for="p in props.productsData.filter(p => p.id !== parentProductId)" :key="p.id"
                                :value="p.id">{{ p.name }} (ID: {{ p.id }})</option>
                        </select>
                    </div>

                    <button type="submit" :disabled="!parentProductId || !childProductId"
                        class="w-full px-4 py-2 border-2 border-copy transition relative -m-0.5 font-bold bg-primary text-primary-content hover:bg-primary-dark rounded-lg shadow-md disabled:opacity-50 disabled:cursor-not-allowed">
                        Assign Relationship
                    </button>
                </form>

                <div class="mt-8 pt-4 border-t border-copy-light/50">
                    <h3 class="font-semibold text-copy mb-2">Legend</h3>
                    <ul class="text-sm text-copy-light space-y-1">
                        <li class="flex items-center"><span class="w-3 h-3 rounded-full mr-2 flex-shrink-0"
                                style="background-color: #1f77b4;"></span>Root Product (No Parent) - Larger node</li>
                        <li class="flex items-center"><span class="w-3 h-3 rounded-full mr-2 flex-shrink-0"
                                style="background-color: #ff7f0e;"></span>Child Product - Smaller node</li>
                        <li class="flex items-center mt-3 text-xs">
                            <svg class="w-4 h-4 mr-2 text-copy-light" fill="none" stroke="currentColor"
                                viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                            </svg>
                            Click node to edit product.
                        </li>
                    </ul>
                </div>
            </div>

            <!-- Visualization Panel (Styled as a Card) -->
            <div class="lg:col-span-2 relative min-h-[600px] rounded-lg border-2 border-copy bg-foreground shadow-lg">
                <div v-if="isLoading"
                    class="absolute inset-0 flex flex-col items-center justify-center rounded-lg z-10 bg-foreground/90">
                    <span class="loading loading-spinner loading-lg text-primary"></span>
                    <p class="ml-3 text-lg text-copy mt-2">Building Network Graph...</p>
                </div>

                <div ref="graphContainer" class="w-full h-full min-h-[600px] overflow-hidden">
                    <!-- D3 SVG will be rendered here -->
                </div>
            </div>

        </div>

    </AdminLayout>
</template>
