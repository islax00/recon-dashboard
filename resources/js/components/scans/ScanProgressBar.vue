<script setup lang="ts">
import { computed } from 'vue';

const props = defineProps<{
    progress: number;
    stage: string;
    message?: string | null;
}>();

const clampedProgress = computed(() =>
    Math.min(100, Math.max(0, props.progress)),
);
</script>

<template>
    <div class="space-y-2">
        <div class="flex items-center justify-between text-sm">
            <span class="font-medium capitalize">{{ stage.replace('_', ' ') }}</span>
            <span class="text-muted-foreground">{{ clampedProgress }}%</span>
        </div>
        <div class="h-2 overflow-hidden rounded-full bg-muted">
            <div
                class="h-full rounded-full bg-primary transition-all duration-500"
                :style="{ width: `${clampedProgress}%` }"
            />
        </div>
        <p v-if="message" class="text-sm text-muted-foreground">
            {{ message }}
        </p>
    </div>
</template>
