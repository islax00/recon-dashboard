<script setup lang="ts">
import { Badge } from '@/components/ui/badge';
import type { ScanStatus } from '@/types';

const { status } = defineProps<{
    status: ScanStatus;
}>();

const variants: Record<
    ScanStatus,
    'default' | 'secondary' | 'destructive' | 'outline'
> = {
    pending: 'secondary',
    running: 'default',
    completed: 'outline',
    failed: 'destructive',
};

const labels: Record<ScanStatus, string> = {
    pending: 'Pending',
    running: 'Running',
    completed: 'Completed',
    failed: 'Failed',
};
</script>

<template>
    <Badge :variant="variants[status]">
        {{ labels[status] }}
    </Badge>
</template>
