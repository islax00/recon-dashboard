<script setup lang="ts">
import { Form, Head, Link } from '@inertiajs/vue3';
import { Globe, Plus } from '@lucide/vue';
import Heading from '@/components/Heading.vue';
import InputError from '@/components/InputError.vue';
import ScanStatusBadge from '@/components/scans/ScanStatusBadge.vue';
import { Button } from '@/components/ui/button';
import {
    Card,
    CardContent,
    CardDescription,
    CardHeader,
    CardTitle,
} from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { index, show, store } from '@/routes/scans';
import type { Scan } from '@/types';

defineOptions({
    layout: {
        breadcrumbs: [
            {
                title: 'Scans',
                href: index(),
            },
        ],
    },
});

defineProps<{
    scans: Scan[];
}>();
</script>

<template>
    <Head title="Scans" />

    <div class="flex flex-col gap-6 p-4">
        <Heading
            title="Reconnaissance Scans"
            description="Launch and monitor domain reconnaissance pipelines."
        />

        <div class="grid gap-6 lg:grid-cols-[360px_1fr]">
            <Card>
                <CardHeader>
                    <CardTitle class="flex items-center gap-2">
                        <Plus class="size-4" />
                        New Scan
                    </CardTitle>
                    <CardDescription>
                        Enter a domain to start the full recon pipeline.
                    </CardDescription>
                </CardHeader>
                <CardContent>
                    <Form
                        v-bind="store.form()"
                        class="space-y-4"
                        v-slot="{ errors, processing }"
                    >
                        <div class="grid gap-2">
                            <Label for="domain">Domain</Label>
                            <Input
                                id="domain"
                                name="domain"
                                placeholder="example.com"
                                required
                            />
                            <InputError :message="errors.domain" />
                        </div>
                        <Button type="submit" class="w-full" :disabled="processing">
                            Start Scan
                        </Button>
                    </Form>
                </CardContent>
            </Card>

            <Card>
                <CardHeader>
                    <CardTitle>Recent Scans</CardTitle>
                    <CardDescription>
                        Click a scan to view live progress, graph, and AI
                        analysis.
                    </CardDescription>
                </CardHeader>
                <CardContent>
                    <div
                        v-if="scans.length === 0"
                        class="flex flex-col items-center justify-center gap-2 py-12 text-center text-muted-foreground"
                    >
                        <Globe class="size-10 opacity-40" />
                        <p>No scans yet. Start your first reconnaissance run.</p>
                    </div>

                    <div v-else class="divide-y divide-sidebar-border/70">
                        <Link
                            v-for="scan in scans"
                            :key="scan.id"
                            :href="show(scan.id)"
                            class="flex items-center justify-between gap-4 py-4 transition-colors first:pt-0 last:pb-0 hover:text-primary"
                        >
                            <div>
                                <p class="font-medium">{{ scan.domain }}</p>
                                <p class="text-sm text-muted-foreground">
                                    {{ new Date(scan.created_at).toLocaleString() }}
                                </p>
                            </div>
                            <ScanStatusBadge :status="scan.status" />
                        </Link>
                    </div>
                </CardContent>
            </Card>
        </div>
    </div>
</template>
