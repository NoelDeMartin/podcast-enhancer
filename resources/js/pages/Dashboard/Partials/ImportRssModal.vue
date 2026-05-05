<script setup lang="ts">
import { useForm } from '@inertiajs/vue3';
import Loader2 from '~icons/lucide/loader-2';

import { store as syncStore } from '@/actions/App/Http/Controllers/FeedSyncController';
import { Button } from '@/components/ui/button';
import { DialogFooter } from '@/components/ui/dialog';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Modal } from '@/components/ui/modal';
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';

const emit = defineEmits<{
    close: [];
}>();

const form = useForm({
    rss_url: '',
    title: '',
    description: '',
    sync_frequency: '0',
});

const submit = () => {
    form.post(syncStore.url(), {
        onSuccess: () => {
            form.reset();
            emit('close');
        },
    });
};
</script>

<template>
    <Modal
        title="Import Feed from RSS"
        description="Create a synchronized feed automatically from an RSS URL."
    >
        <form @submit.prevent="submit">
            <div class="grid gap-4 py-4">
                <div class="grid gap-2">
                    <Label for="rss_url">RSS URL <span class="text-red-500">*</span></Label>
                    <Input
                        id="rss_url"
                        v-model="form.rss_url"
                        placeholder="https://example.com/feed.xml"
                        required
                    />
                    <div v-if="form.errors.rss_url" class="text-sm text-red-500">
                        {{ form.errors.rss_url }}
                    </div>
                </div>
                <div class="grid gap-2">
                    <Label for="sync_frequency">Sync Frequency</Label>
                    <Select v-model="form.sync_frequency">
                        <SelectTrigger id="sync_frequency">
                            <SelectValue placeholder="Select frequency" />
                        </SelectTrigger>
                        <SelectContent>
                            <SelectItem value="0">Manual only</SelectItem>
                            <SelectItem value="3600">Every hour</SelectItem>
                            <SelectItem value="21600">Every 6 hours</SelectItem>
                            <SelectItem value="43200">Every 12 hours</SelectItem>
                            <SelectItem value="86400">Daily</SelectItem>
                            <SelectItem value="604800">Weekly</SelectItem>
                        </SelectContent>
                    </Select>
                    <div v-if="form.errors.sync_frequency" class="text-sm text-red-500">
                        {{ form.errors.sync_frequency }}
                    </div>
                </div>
                <div class="text-muted-foreground text-xs">
                    Note: Synchronized feeds will not automatically transcribe imported episodes to
                    reduce server load.
                </div>
            </div>
            <DialogFooter>
                <Button type="submit" :disabled="form.processing">
                    <Loader2 v-if="form.processing" class="mr-2 h-4 w-4 animate-spin" />
                    Import Feed
                </Button>
            </DialogFooter>
        </form>
    </Modal>
</template>
