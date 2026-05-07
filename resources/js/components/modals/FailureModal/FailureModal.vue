<script setup lang="ts">
import { DialogHeader, DialogTitle } from '@/components/ui/dialog';
import { Modal } from '@/components/ui/modal';

withDefaults(
    defineProps<{
        title: string;
        description?: string | null;
        exception?: string | null;
    }>(),
    {
        description: null,
        exception: null,
    },
);
</script>

<template>
    <Modal class="sm:max-w-[720px]">
        <div class="min-w-0 space-y-6">
            <DialogHeader>
                <div class="flex flex-col items-center gap-4 text-left">
                    <div
                        class="bg-destructive shadow-neo-hard border-neo-dark flex size-12 shrink-0 -rotate-3 items-center justify-center border-3"
                    >
                        <i-carbon-warning-alt-filled class="size-6 text-white" />
                    </div>

                    <div class="space-y-2 pt-1 text-center">
                        <DialogTitle class="text-xl leading-tight font-black tracking-tight">
                            {{ title }}
                        </DialogTitle>
                        <div v-if="description" class="text-muted-foreground text-sm">
                            {{ description }}
                        </div>
                    </div>
                </div>
            </DialogHeader>

            <div class="space-y-2">
                <div class="text-muted-foreground text-xs font-medium tracking-wide">
                    Exception details
                </div>
                <div
                    class="border-neo-dark bg-muted/30 max-w-full overflow-hidden rounded-none border-3"
                >
                    <div class="max-h-[50vh] min-w-0 overflow-auto">
                        <pre
                            class="text-neo-dark/90 w-fit min-w-full p-4 font-mono text-xs leading-relaxed whitespace-pre dark:text-white/90"
                            >{{ exception ?? 'No exception details available.' }}</pre
                        >
                    </div>
                </div>
            </div>
        </div>
    </Modal>
</template>
