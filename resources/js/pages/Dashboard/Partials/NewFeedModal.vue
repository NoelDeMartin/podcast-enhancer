<script setup lang="ts">
import { useForm } from '@inertiajs/vue3';
import { ref } from 'vue';
import Add from '~icons/carbon/add';
import Renew from '~icons/carbon/renew';
import Rss from '~icons/carbon/rss';

import { store } from '@/actions/App/Http/Controllers/FeedController';
import { store as syncStore } from '@/actions/App/Http/Controllers/FeedSyncController';
import InputError from '@/components/InputError.vue';
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
import { Textarea } from '@/components/ui/textarea';

const props = defineProps<{
    canCreateManual?: boolean;
    canUploadFiles?: boolean;
}>();

const emit = defineEmits<{
    close: [];
}>();

const mode = ref<'rss' | 'manual'>('rss');

const manualForm = useForm({
    title: '',
    description: '',
    image_url: '',
    image_file: null as File | null,
});

const rssForm = useForm({
    rss_url: '',
    title: '',
    description: '',
});

const imageSource = ref<'url' | 'file'>('url');

const submitManual = () => {
    manualForm.post(store.url(), {
        onSuccess: () => {
            manualForm.reset();
            emit('close');
        },
    });
};

const submitRss = () => {
    rssForm.post(syncStore.url(), {
        onSuccess: () => {
            rssForm.reset();
            emit('close');
        },
    });
};

const tabs = [
    { value: 'rss', Icon: Rss, label: 'Import from RSS' },
    { value: 'manual', Icon: Add, label: 'Manual Creation', hidden: !props.canCreateManual },
] as const;

const visibleTabs = tabs.filter((tab) => !('hidden' in tab && tab.hidden));
</script>

<template>
    <Modal
        title="Add New Podcast"
        :show-close-button="false"
        :description="
            mode === 'rss'
                ? 'Import a podcast from an existing feed.'
                : 'Manually add a new podcast to start tracking episodes.'
        "
    >
        <div v-if="visibleTabs.length > 1" class="flex w-full gap-1 border-b-3 border-black p-0.5">
            <button
                v-for="{ value, Icon, label } in visibleTabs"
                :key="value"
                type="button"
                @click="mode = value"
                :class="[
                    'flex flex-1 items-center justify-center rounded-none py-2.5 transition-all duration-200',
                    mode === value
                        ? 'bg-black text-white'
                        : 'text-neutral-500 hover:bg-neutral-100 hover:text-black',
                ]"
            >
                <component
                    :is="Icon"
                    :class="['mr-2 size-4 transition-transform', mode === value ? 'scale-110' : '']"
                />
                <span class="text-xs font-black tracking-widest uppercase">{{ label }}</span>
            </button>
        </div>

        <!-- RSS Import Form -->
        <form v-if="mode === 'rss'" @submit.prevent="submitRss">
            <div class="grid gap-4 py-4">
                <div class="grid gap-2">
                    <Label for="rss_url">RSS URL <span class="text-red-500">*</span></Label>
                    <span class="sr-only text-xs text-neutral-500 italic"
                        >Example: https://example.com/podcast.xml</span
                    >
                    <Input
                        id="rss_url"
                        v-model="rssForm.rss_url"
                        placeholder="https://example.com/podcast.xml"
                        required
                    />
                    <InputError :message="rssForm.errors.rss_url" />
                </div>
            </div>
            <DialogFooter>
                <Button
                    type="button"
                    variant="outline"
                    :disabled="rssForm.processing"
                    @click="emit('close')"
                >
                    Cancel
                </Button>
                <Button type="submit" :disabled="rssForm.processing">
                    <Renew v-if="rssForm.processing" class="mr-2 size-4 animate-spin" />
                    Import Podcast
                </Button>
            </DialogFooter>
        </form>

        <!-- Manual Creation Form -->
        <form v-else @submit.prevent="submitManual">
            <div class="grid gap-4 py-4">
                <div class="grid gap-2">
                    <Label for="title">Title</Label>
                    <Input
                        id="title"
                        v-model="manualForm.title"
                        placeholder="Enter podcast title..."
                        required
                    />
                    <InputError :message="manualForm.errors.title" />
                </div>
                <div class="grid gap-2">
                    <Label for="description">Description</Label>
                    <Textarea
                        id="description"
                        v-model="manualForm.description"
                        placeholder="Optional description for this podcast..."
                        rows="3"
                    />
                    <InputError :message="manualForm.errors.description" />
                </div>
                <div class="grid gap-2">
                    <Label for="imageSource">Image Source</Label>
                    <Select v-model="imageSource">
                        <SelectTrigger>
                            <SelectValue placeholder="Select source" />
                        </SelectTrigger>
                        <SelectContent>
                            <SelectItem value="url">Remote URL</SelectItem>
                            <SelectItem v-if="canUploadFiles" value="file">Upload File</SelectItem>
                        </SelectContent>
                    </Select>
                </div>
                <div v-if="imageSource === 'url'" class="grid gap-2">
                    <Label for="image_url">Image URL</Label>
                    <Input
                        id="image_url"
                        v-model="manualForm.image_url"
                        placeholder="https://example.com/image.jpg"
                    />
                    <InputError :message="manualForm.errors.image_url" />
                </div>
                <div v-if="imageSource === 'file'" class="grid gap-2">
                    <Label for="image_file">Image File</Label>
                    <Input
                        id="image_file"
                        type="file"
                        @input="
                            manualForm.image_file =
                                ($event.target as HTMLInputElement).files?.[0] || null
                        "
                        accept="image/*"
                    />
                    <InputError :message="manualForm.errors.image_file" />
                </div>
            </div>
            <DialogFooter>
                <Button
                    type="button"
                    variant="outline"
                    :disabled="manualForm.processing"
                    @click="emit('close')"
                >
                    Cancel
                </Button>
                <Button type="submit" :disabled="manualForm.processing"> Create Podcast </Button>
            </DialogFooter>
        </form>
    </Modal>
</template>
