<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import { ArrowRight, Radar } from '@lucide/vue';
import { Button } from '@/components/ui/button';
import {
    Card,
    CardContent,
    CardDescription,
    CardHeader,
    CardTitle,
} from '@/components/ui/card';
import { dashboard } from '@/routes';
import { index as scansIndex } from '@/routes/scans';

defineOptions({
    layout: {
        breadcrumbs: [
            {
                title: 'Dashboard',
                href: dashboard(),
            },
        ],
    },
});
</script>

<template>
    <Head title="Dashboard" />

    <div class="flex h-full flex-1 flex-col gap-4 p-4">
        <Card class="border-primary/20 bg-gradient-to-br from-primary/5 to-transparent">
            <CardHeader>
                <CardTitle class="flex items-center gap-2 text-2xl">
                    <Radar class="size-6" />
                    Recon Dashboard
                </CardTitle>
                <CardDescription class="max-w-2xl text-base">
                    Launch subdomain discovery, crawling, fingerprinting, and
                    secret analysis pipelines. Monitor progress live and explore
                    the attack surface in 3D.
                </CardDescription>
            </CardHeader>
            <CardContent>
                <Button as-child>
                    <Link :href="scansIndex()" class="inline-flex items-center gap-2">
                        Open Scans
                        <ArrowRight class="size-4" />
                    </Link>
                </Button>
            </CardContent>
        </Card>
    </div>
</template>
