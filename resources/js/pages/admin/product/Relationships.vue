<script setup lang="ts">
import AdminLayout from '@/layouts/AdminLayout.vue';
import { Head, router } from '@inertiajs/vue3';
import { computed, onMounted, ref, watch } from 'vue';
import * as d3 from 'd3'; // Requires 'd3' npm package

// 1. Typescript Declaration for route helper (Critical for fix from previous steps)
import type { Route } from 'ziggy-js';
declare const route: Route;

// 2. Define Props
interface ProductNode {
    id: number;
    name: string;
    parent_id: number | null;
    is_root: boolean;
}

const props = defineProps<{
    productsData: ProductNode[];
}>();

// 3. State Management
const graphContainer = ref<HTMLElement | null>(null);
const isLoading = ref(true);

const nodes = computed(() => {
    return props.productsData.map(p => ({
        ...p,
        // D3 requires a mutable x/y position, and we add a 'group' for color coding
        x: 0,
        y: 0,
        is_root: p.parent_id === null,
        group: p.parent_id === null ? 1 : 2 // 1: Root, 2: Child
    }));
});

const links = computed(() => {
    const linkSet = props.productsData
        .filter(p => p.parent_id !== null)
        .map(p => ({
            source: p.parent_id,
            target: p.id,
        }));

    // Convert source/target IDs to actual node objects for D3 simulation
    const nodeMap = new Map(nodes.value.map(n => [n.id, n]));

    return linkSet.map(link => ({
        source: nodeMap.get(link.source), // Source node object (Parent)
        target: nodeMap.get(link.target), // Target node object (Child)
    })).filter(link => link.source && link.target); // Filter out links with missing nodes
});


// 4. D3 Visualization Logic
const initializeGraph = () => {
    if (!graphContainer.value) return;

    isLoading.value = true;

    const container = graphContainer.value;

    // Clear any previous SVG
    d3.select(container).selectAll("svg").remove();

    const width = container.clientWidth;
    const height = Math.max(container.clientHeight, 600); // Minimum height

    const svg = d3.select(container)
        .append("svg")
        .attr("viewBox", `0 0 ${width} ${height}`)
        .style("width", "100%")
        .style("height", "100%")
        .attr("class", "bg-white dark:bg-gray-800 rounded-xl shadow-lg border border-gray-200 dark:border-gray-700");

    // Central container for pan/zoom
    const g = svg.append("g");

    // Add zoom and pan functionality
    const zoom = d3.zoom()
        .scaleExtent([0.1, 4])
        .on("zoom", (event) => {
            g.attr("transform", event.transform);
        });

    svg.call(zoom as any);

    // D3 Force Simulation
    const simulation = d3.forceSimulation(nodes.value as d3.SimulationNodeDatum[])
        .force("link", d3.forceLink(links.value as d3.SimulationLinkDatum<d3.SimulationNodeDatum>[])
            .id((d: any) => d.id)
            .distance(150) // Link distance
        )
        .force("charge", d3.forceManyBody().strength(-300)) // Repel nodes
        .force("center", d3.forceCenter(width / 2, height / 2))
        .on("tick", ticked);

    // Color scale for root vs child nodes
    const color = d3.scaleOrdinal(d3.schemeCategory10);

    // 4.1. Links (Lines)
    const link = g.append("g")
        .attr("stroke", "currentColor")
        .attr("stroke-opacity", 0.6)
        .attr("class", "text-gray-500 dark:text-gray-400")
        .selectAll("line")
        .data(links.value)
        .join("line")
        .attr("stroke-width", 2);

    // 4.2. Nodes (Circles)
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
            // Optional: Navigate to product edit page on click
            router.get(route('admin.products.edit', d.id));
        })
        .call(d3.drag<any, any>()
            .on("start", dragstarted)
            .on("drag", dragged)
            .on("end", dragended));

    // 4.3. Labels (Text)
    const label = g.append("g")
        .attr("class", "text-sm font-semibold text-gray-800 dark:text-gray-200 pointer-events-none select-none")
        .selectAll("text")
        .data(nodes.value)
        .join("text")
        .attr("x", 15) // Offset from circle
        .attr("y", 5)
        .text((d: any) => d.name)
        .attr("font-family", "Inter, sans-serif")
        .attr("font-size", 12);

    // 4.4. Tick Function (Updates position on each simulation tick)
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

    // 4.5. Drag Handlers
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
        event.subject.fx = null; // Release node so it continues to move naturally
        event.subject.fy = null;
    }

    // Set loading false after initial rendering
    setTimeout(() => isLoading.value = false, 500);
};

// 5. Lifecycle Hooks
onMounted(() => {
    initializeGraph();
    // Re-initialize graph on window resize to make it responsive
    window.addEventListener('resize', initializeGraph);
});

// 6. Cleanup
// Note: In a real app, you would add an onBeforeUnmount cleanup function for the event listener.

</script>

<template>
    <AdminLayout>
        <Head title="Product Relationships" />

        <div class="p-6 md:p-10 space-y-8">
            <header class="flex justify-between items-center border-b pb-4 dark:border-gray-700">
                <h1 class="text-3xl font-extrabold text-gray-900 dark:text-gray-100">
                    Product Relationship Visualizer
                </h1>
                <Link
                    :href="route('admin.products.index')"
                    class="btn btn-sm btn-ghost text-primary hover:text-primary-dark"
                >
                    &larr; Back to Product Index
                </Link>
            </header>

            <div class="bg-warning text-warning-content p-4 rounded-xl shadow-md">
                <p class="font-bold">Backend Changes Required:</p>
                <p class="text-sm">To fully enable this feature, you must add the `parent_id` column to your `products` migration and update your `ProductController` to allow setting the parent product during create/edit.</p>
            </div>

            <!-- Mass Assignment / Control Panel Section -->
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

                <!-- Mass Assignment Panel (Future Development) -->
                <div class="lg:col-span-1 bg-white dark:bg-gray-800 p-6 rounded-xl shadow-lg border border-gray-200 dark:border-gray-700 h-full">
                    <h2 class="text-xl font-bold mb-4 dark:text-gray-100">Mass Assignment Controls</h2>
                    <p class="text-gray-600 dark:text-gray-400 mb-6">
                        Use this area to mass assign parent-child relationships using selection fields.
                    </p>

                    <div class="space-y-4">
                        <select class="select select-bordered w-full">
                            <option disabled selected>Select Parent Product</option>
                            <option v-for="p in props.productsData" :key="p.id" :value="p.id">{{ p.name }} (Parent)</option>
                        </select>
                        <select class="select select-bordered w-full">
                            <option disabled selected>Select Child Product</option>
                            <option v-for="p in props.productsData" :key="p.id" :value="p.id">{{ p.name }} (Child)</option>
                        </select>
                        <button class="btn btn-primary w-full">Assign Relationship</button>
                    </div>

                    <div class="mt-8 pt-4 border-t dark:border-gray-700">
                        <h3 class="font-semibold dark:text-gray-100">Legend</h3>
                        <ul class="text-sm text-gray-600 dark:text-gray-400 space-y-1 mt-2">
                            <li class="flex items-center"><span class="w-3 h-3 rounded-full mr-2" style="background-color: #1f77b4;"></span>Root Product (No Parent)</li>
                            <li class="flex items-center"><span class="w-3 h-3 rounded-full mr-2" style="background-color: #ff7f0e;"></span>Child Product</li>
                        </ul>
                    </div>
                </div>

                <!-- Visualization Panel -->
                <div class="lg:col-span-2 relative min-h-[600px]">
                    <div v-if="isLoading" class="absolute inset-0 flex items-center justify-center bg-white dark:bg-gray-800/80 rounded-xl z-10">
                        <span class="loading loading-spinner loading-lg text-primary"></span>
                        <p class="ml-3 text-lg dark:text-gray-300">Building Network Graph...</p>
                    </div>

                    <div ref="graphContainer" class="w-full h-full min-h-[600px] overflow-hidden">
                        <!-- D3 SVG will be rendered here -->
                    </div>
                </div>

            </div>

        </div>

    </AdminLayout>
</template>

<!-- Load D3.js library -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/d3/7.8.5/d3.min.js"></script>
