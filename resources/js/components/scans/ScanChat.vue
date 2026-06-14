<script setup lang="ts">
import { useHttp } from '@inertiajs/vue3';
import { LoaderCircle, Send } from '@lucide/vue';
import { ref } from 'vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { chat } from '@/routes/scans';
import type { ChatMessage, Scan } from '@/types';

const { scan } = defineProps<{
    scan: Scan;
}>();

const messages = ref<ChatMessage[]>([]);
const http = useHttp<{ message: string }, { answer: string }>({
    message: '',
});

async function sendMessage() {
    const message = http.message.trim();

    if (!message || http.processing) {
        return;
    }

    messages.value.push({ role: 'user', content: message });
    http.message = '';

    const response = await http.post(chat.url(scan.id));

    messages.value.push({
        role: 'assistant',
        content: response.answer,
    });
}
</script>

<template>
    <div class="flex h-[420px] flex-col rounded-xl border border-sidebar-border/70 dark:border-sidebar-border">
        <div class="flex-1 space-y-3 overflow-y-auto p-4">
            <p
                v-if="messages.length === 0"
                class="text-sm text-muted-foreground"
            >
                Ask questions about this scan's findings, risk score, or
                attack surface.
            </p>
            <div
                v-for="(message, index) in messages"
                :key="index"
                class="rounded-lg px-3 py-2 text-sm"
                :class="
                    message.role === 'user'
                        ? 'ml-8 bg-primary text-primary-foreground'
                        : 'mr-8 bg-muted text-foreground'
                "
            >
                {{ message.content }}
            </div>
        </div>

        <form
            class="flex items-center gap-2 border-t border-sidebar-border/70 p-3 dark:border-sidebar-border"
            @submit.prevent="sendMessage"
        >
            <Input
                v-model="http.message"
                placeholder="Ask about this scan..."
                autocomplete="off"
            />
            <Button type="submit" size="icon" :disabled="http.processing">
                <LoaderCircle
                    v-if="http.processing"
                    class="size-4 animate-spin"
                />
                <Send v-else class="size-4" />
            </Button>
        </form>
    </div>
</template>
