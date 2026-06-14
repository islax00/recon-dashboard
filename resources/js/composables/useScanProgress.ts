import { router } from '@inertiajs/vue3';
import { useEcho } from '@laravel/echo-vue';
import { computed, ref, unref, watch, type MaybeRef } from 'vue';
import type { Scan, ScanProgressEvent, ScanStatus } from '@/types';

export function useScanProgress(scan: MaybeRef<Scan>) {
    const status = ref<ScanStatus>(unref(scan).status);
    const stage = ref('pipeline');
    const progress = ref(unref(scan).status === 'completed' ? 100 : 0);
    const message = ref<string | null>(null);

    const isActive = computed(
        () => status.value === 'pending' || status.value === 'running',
    );

    useEcho(
        `scans.${unref(scan).id}`,
        '.scan.progress.updated',
        (payload: ScanProgressEvent) => {
            status.value = payload.status;
            stage.value = payload.stage;
            progress.value = payload.progress;
            message.value = payload.message;

            if (payload.status === 'completed' || payload.status === 'failed') {
                router.reload({
                    only: ['scan', 'report', 'graph', 'stats'],
                });
            }
        },
    );

    watch(
        () => unref(scan).status,
        (value) => {
            status.value = value;

            if (value === 'completed') {
                progress.value = 100;
            }
        },
    );

    let pollTimer: ReturnType<typeof setInterval> | undefined;

    watch(
        isActive,
        (active) => {
            if (pollTimer) {
                clearInterval(pollTimer);
                pollTimer = undefined;
            }

            if (!active) {
                return;
            }

            pollTimer = setInterval(() => {
                router.reload({
                    only: ['scan', 'report', 'graph', 'stats'],
                });
            }, 5000);
        },
        { immediate: true },
    );

    return {
        status,
        stage,
        progress,
        message,
        isActive,
    };
}
