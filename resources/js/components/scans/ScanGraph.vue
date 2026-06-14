<script setup lang="ts">
import ForceGraph3D from '3d-force-graph';
import { onMounted, onUnmounted, ref, watch } from 'vue';
import type { GraphData } from '@/types';

type GraphNodeObject = {
    id: string;
    name: string;
    type: string;
    val: number;
    color: string;
};

const props = defineProps<{
    graph: GraphData;
}>();

const container = ref<HTMLDivElement | null>(null);

// eslint-disable-next-line @typescript-eslint/no-explicit-any
let graphInstance: any = null;

const nodeColors: Record<string, string> = {
    domain: '#3b82f6',
    subdomain: '#06b6d4',
    ip: '#8b5cf6',
    endpoint: '#22c55e',
    js_file: '#f97316',
    technology: '#ec4899',
};

function transformData(graph: GraphData) {
    return {
        nodes: graph.nodes.map((node) => ({
            id: node.id,
            name: node.label,
            type: node.type,
            val: node.type === 'domain' ? 8 : 4,
            color: nodeColors[node.type] ?? '#94a3b8',
        })),
        links: graph.edges.map((edge) => ({
            source: edge.source,
            target: edge.target,
            relation: edge.relation,
        })),
    };
}

function renderGraph() {
    if (!container.value) {
        return;
    }

    if (graphInstance) {
        graphInstance.graphData(transformData(props.graph));

        return;
    }

    graphInstance = new ForceGraph3D(container.value)
        .backgroundColor('#0f172a')
        .graphData(transformData(props.graph))
        .nodeLabel((node) => {
            const graphNode = node as GraphNodeObject;

            return `${graphNode.name} (${graphNode.type})`;
        })
        .nodeColor((node) => (node as GraphNodeObject).color)
        .linkOpacity(0.35)
        .linkWidth(1)
        .enableNodeDrag(true)
        .width(container.value.clientWidth)
        .height(480);
}

onMounted(() => {
    renderGraph();
});

watch(
    () => props.graph,
    () => {
        renderGraph();
    },
    { deep: true },
);

onUnmounted(() => {
    if (container.value) {
        container.value.innerHTML = '';
    }

    graphInstance = null;
});
</script>

<template>
    <div
        ref="container"
        class="h-[480px] w-full overflow-hidden rounded-xl border border-sidebar-border/70 bg-slate-950 dark:border-sidebar-border"
    />
</template>
