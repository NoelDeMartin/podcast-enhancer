<script setup lang="ts">
import { useForm } from '@inertiajs/vue3';

import { destroy } from '@/actions/App/Http/Controllers/FeedController';
import { Button } from '@/components/ui/button';
import { DialogFooter } from '@/components/ui/dialog';
import { Modal } from '@/components/ui/modal';

const props = defineProps<{
    feed: any;
}>();

const emit = defineEmits<{
    close: [];
}>();

const form = useForm({});

const submit = () => {
    if (!props.feed) {
        return;
    }

    form.delete(destroy.url(props.feed.slug), {
        onFinish: () => {
            emit('close');
        },
    });
};
</script>

<template>
    <Modal title="Delete feed" description="This is a dangerous operation and cannot be undone.">
        <form @submit.prevent="submit" class="space-y-6">
            <div
                class="space-y-3 rounded-none border-3 bg-red-50 p-4 text-sm text-red-700 dark:border-red-200/10 dark:bg-red-700/10 dark:text-red-100"
            >
                <div class="space-y-1">
                    <div class="font-medium">You are about to permanently delete:</div>
                    <div class="font-semibold">
                        {{ feed?.title ?? 'This feed' }}
                    </div>
                </div>
                <div>
                    This will also permanently delete
                    <span class="font-semibold">
                        {{ feed?.entries_count ?? 0 }}
                    </span>
                    {{ (feed?.entries_count ?? 0) === 1 ? 'entry' : 'entries' }}.
                </div>
            </div>

            <DialogFooter class="gap-2">
                <Button
                    type="button"
                    variant="secondary"
                    :disabled="form.processing"
                    @click="emit('close')"
                >
                    Cancel
                </Button>
                <Button type="submit" variant="destructive" :disabled="form.processing">
                    Delete feed
                </Button>
            </DialogFooter>
        </form>
    </Modal>
</template>
