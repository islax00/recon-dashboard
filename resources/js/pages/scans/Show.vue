<script setup lang="ts">
import { Head, Link, setLayoutProps } from '@inertiajs/vue3';
import {
    AlertTriangle,
    ArrowLeft,
    Globe,
    KeyRound,
    Network,
    Server,
} from '@lucide/vue';
import { computed, toRef, watchEffect } from 'vue';
import Heading from '@/components/Heading.vue';
import ScanChat from '@/components/scans/ScanChat.vue';
import ScanGraph from '@/components/scans/ScanGraph.vue';
import ScanProgressBar from '@/components/scans/ScanProgressBar.vue';
import ScanStatusBadge from '@/components/scans/ScanStatusBadge.vue';
import {
    Card,
    CardContent,
    CardDescription,
    CardHeader,
    CardTitle,
} from '@/components/ui/card';
import { useScanProgress } from '@/composables/useScanProgress';
import { index, show } from '@/routes/scans';
import type { GraphData, Report, Scan, ScanStats } from '@/types';

const props = defineProps<{
    scan: Scan;
    report: Report | null;
    graph: GraphData;
    stats: ScanStats;
}>();

watchEffect(() => {
    setLayoutProps({
        breadcrumbs: [
            {
                title: 'Scans',
                href: index(),
            },
            {
                title: props.scan.domain,
                href: show(props.scan.id),
            },
        ],
    });
});

const scanRef = toRef(props, 'scan');
const { status, stage, progress, message, isActive } = useScanProgress(scanRef);

const statCards = computed(() => [
    {
        label: 'Subdomains',
        value: props.stats.subdomains,
        icon: Globe,
    },
    {
        label: 'Endpoints',
        value: props.stats.endpoints,
        icon: Network,
    },
    {
        label: 'Secrets',
        value: props.stats.secrets,
        icon: KeyRound,
    },
    {
        label: 'Technologies',
        value: props.stats.technologies,
        icon: Server,
    },
]);
</script>

<template>
    <Head :title="`Scan: ${scan.domain}`" />

    <div class="flex flex-col gap-6 p-4">
        <div class="flex flex-wrap items-start justify-between gap-4">
            <div class="space-y-2">
                <Link
                    :href="index()"
                    class="inline-flex items-center gap-1 text-sm text-muted-foreground hover:text-foreground"
                >
                    <ArrowLeft class="size-4" />
                    Back to scans
                </Link>
                <Heading
                    :title="scan.domain"
                    description="Live reconnaissance results and attack surface graph."
                />
            </div>
            <ScanStatusBadge :status="status" />
        </div>

        <Card v-if="isActive">
            <CardHeader>
                <CardTitle>Pipeline Progress</CardTitle>
                <CardDescription>
                    Updates in real time via Reverb while the scan runs.
                </CardDescription>
            </CardHeader>
            <CardContent>
                <ScanProgressBar
                    :progress="progress"
                    :stage="stage"
                    :message="message"
                />
            </CardContent>
        </Card>

        <div class="grid gap-4 md:grid-cols-2 xl:grid-cols-4">
            <Card v-for="item in statCards" :key="item.label">
                <CardHeader class="pb-2">
                    <CardDescription class="flex items-center gap-2">
                        <component :is="item.icon" class="size-4" />
                        {{ item.label }}
                    </CardDescription>
                    <CardTitle class="text-3xl">{{ item.value }}</CardTitle>
                </CardHeader>
            </Card>
        </div>

        <div class="grid gap-6 xl:grid-cols-[1.4fr_1fr]">
            <Card>
                <CardHeader>
                    <CardTitle>Attack Surface Graph</CardTitle>
                    <CardDescription>
                        Interactive 3D view of domains, endpoints, and
                        relationships.
                    </CardDescription>
                </CardHeader>
                <CardContent>
                    <ScanGraph :graph="graph" />
                </CardContent>
            </Card>

            <div class="flex flex-col gap-6">
                <Card v-if="report">
                    <CardHeader>
                        <CardTitle class="flex items-center gap-2">
                            <AlertTriangle class="size-4" />
                            Risk Report
                        </CardTitle>
                        <CardDescription>
                            Score {{ report.risk_score }}/100 ·
                            {{ report.risk_level }}
                        </CardDescription>
                    </CardHeader>
                    <CardContent class="space-y-4">
                        <p class="text-sm text-muted-foreground">
                            {{ report.ai_summary }}
                        </p>
                        <ul
                            v-if="report.findings?.length"
                            class="space-y-2 text-sm"
                        >
                            <li
                                v-for="finding in report.findings"
                                :key="finding.id"
                                class="rounded-lg border border-sidebar-border/70 p-3 dark:border-sidebar-border"
                            >
                                <p class="font-medium">{{ finding.title }}</p>
                                <p class="text-muted-foreground">
                                    {{ finding.description }}
                                </p>
                            </li>
                        </ul>
                    </CardContent>
                </Card>

                <Card>
                    <CardHeader>
                        <CardTitle>Intelligence Chat</CardTitle>
                        <CardDescription>
                            Ask AI about findings and next steps.
                        </CardDescription>
                    </CardHeader>
                    <CardContent>
                        <ScanChat :scan="scan" />
                    </CardContent>
                </Card>
            </div>
        </div>

        <Card v-if="scan.logs?.length">
            <CardHeader>
                <CardTitle>Scan Logs</CardTitle>
            </CardHeader>
            <CardContent>
                <ul class="space-y-2 text-sm">
                    <li
                        v-for="log in scan.logs"
                        :key="log.id"
                        class="flex items-start justify-between gap-4 rounded-lg bg-muted/50 px-3 py-2"
                    >
                        <span>
                            <span class="font-medium capitalize">{{
                                log.tool
                            }}</span>
                            — {{ log.message }}
                        </span>
                        <span class="shrink-0 text-muted-foreground">
                            {{ log.level }}
                        </span>
                    </li>
                </ul>
            </CardContent>
        </Card>
    </div>
</template>
