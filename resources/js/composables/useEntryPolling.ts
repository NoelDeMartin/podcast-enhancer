import { usePoll } from '@inertiajs/vue3';
import { watch } from 'vue';
import type { ComputedRef } from 'vue';

export function useEntryPolling(
    isProcessing: ComputedRef<boolean>,
    only: string[] = ['entry'],
) {
    const { start, stop } = usePoll(3000, { only }, { autoStart: false });

    watch(
        isProcessing,
        (active) => {
            if (active) {
                start();
            } else {
                stop();
            }
        },
        { immediate: true },
    );

    return { start, stop };
}
