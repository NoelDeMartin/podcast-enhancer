<script setup lang="ts">
import { useForm } from '@inertiajs/vue3';

import { destroy } from '@/actions/App/Http/Controllers/FeedController';
import { Button } from '@/components/ui/button';
import { DialogFooter, DialogHeader, DialogTitle, DialogDescription } from '@/components/ui/dialog';
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
    <Modal>
        <form @submit.prevent="submit" class="space-y-6">
            <DialogHeader>
                <div class="flex flex-col items-center gap-4 text-left">
                    <div
                        class="bg-destructive shadow-neo-hard border-neo-dark flex size-12 shrink-0 -rotate-3 items-center justify-center border-3"
                    >
                        <i-carbon-warning-alt-filled class="size-6 text-white" />
                    </div>
                    <div class="space-y-2 pt-1 text-center">
                        <DialogTitle class="text-xl leading-tight font-black tracking-tight">
                            Delete "{{ feed?.title ?? 'this podcast' }}"?
                        </DialogTitle>
                        <DialogDescription
                            class="text-muted-foreground text-sm font-medium text-balance"
                        >
                            This action is irreversible and will permanently
                            <strong class="text-destructive font-bold">
                                remove
                                {{ feed?.entries_count ?? 0 }}
                                {{
                                    (feed?.entries_count ?? 0) === 1 ? 'episode' : 'episodes'
                                }} </strong
                            >.
                        </DialogDescription>
                    </div>
                </div>
            </DialogHeader>

            <DialogFooter class="sm:justify-center">
                <Button
                    type="button"
                    variant="outline"
                    :disabled="form.processing"
                    @click="emit('close')"
                >
                    Cancel
                </Button>
                <Button type="submit" variant="destructive" :disabled="form.processing">
                    Yes, delete podcast
                </Button>
            </DialogFooter>
        </form>
    </Modal>
</template>
