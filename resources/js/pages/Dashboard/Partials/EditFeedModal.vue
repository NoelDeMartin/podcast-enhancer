<script setup lang="ts">
import { useForm } from '@inertiajs/vue3';
import { ref, watch } from 'vue';

import { update } from '@/actions/App/Http/Controllers/FeedController';
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
    feed: any;
    canUploadFiles?: boolean;
}>();

const emit = defineEmits<{
    close: [];
}>();

const form = useForm({
    title: '',
    description: '',
    image_url: '',
    image_file: null as File | null,
    delete_image_file: false,
});

const imageSource = ref<'url' | 'file'>('url');

const isExternal = (url: string | null) => {
    if (!url) {
        return false;
    }
    return url.startsWith('http://') || url.startsWith('https://');
};

watch(
    () => props.feed,
    (feed) => {
        if (!feed) {
            return;
        }

        form.title = feed.title;
        form.description = feed.description ?? '';

        const external = isExternal(feed.image_url);
        imageSource.value = external ? 'url' : 'file';
        form.image_url = external ? feed.image_url : '';
        form.image_file = null;
        form.delete_image_file = false;
    },
    { immediate: true },
);

const submit = () => {
    form.put(update.url(props.feed.slug), {
        onSuccess: () => {
            emit('close');
        },
    });
};
</script>

<template>
    <Modal title="Edit Podcast" description="Update the details of this podcast.">
        <form @submit.prevent="submit">
            <div class="grid gap-4 py-4">
                <div class="grid gap-2">
                    <Label for="edit-title">Title</Label>
                    <Input id="edit-title" v-model="form.title" required />
                    <div v-if="form.errors.title" class="text-sm text-red-500">
                        {{ form.errors.title }}
                    </div>
                </div>
                <div class="grid gap-2">
                    <Label for="edit-description">Description</Label>
                    <Textarea
                        id="edit-description"
                        v-model="form.description"
                        placeholder="Optional description for this podcast..."
                        rows="3"
                    />
                    <div v-if="form.errors.description" class="text-sm text-red-500">
                        {{ form.errors.description }}
                    </div>
                </div>
                <div class="grid gap-2">
                    <Label for="editFeedImageSource">Image Source</Label>
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
                    <Label for="edit-image_url">Image URL</Label>
                    <Input
                        id="edit-image_url"
                        v-model="form.image_url"
                        placeholder="https://example.com/image.jpg"
                    />
                    <div v-if="form.errors.image_url" class="text-sm text-red-500">
                        {{ form.errors.image_url }}
                    </div>
                </div>
                <div v-if="imageSource === 'file'" class="grid gap-2">
                    <Label for="edit-image_file">Image File</Label>
                    <div
                        v-if="
                            !isExternal(feed?.image_url) &&
                            feed?.image_url &&
                            !form.delete_image_file &&
                            !form.image_file
                        "
                        class="flex items-center gap-4"
                    >
                        <span class="text-sm text-gray-500">Current image attached</span>
                        <Button
                            type="button"
                            variant="destructive"
                            size="sm"
                            @click="form.delete_image_file = true"
                        >
                            Delete
                        </Button>
                    </div>
                    <div v-else>
                        <Input
                            id="edit-image_file"
                            type="file"
                            @input="form.image_file = $event.target.files[0]"
                            accept="image/*"
                        />
                        <div v-if="form.errors.image_file" class="text-sm text-red-500">
                            {{ form.errors.image_file }}
                        </div>
                        <Button
                            v-if="
                                form.delete_image_file &&
                                !isExternal(feed?.image_url) &&
                                feed?.image_url
                            "
                            type="button"
                            variant="link"
                            size="sm"
                            class="mt-1 px-0 text-gray-500"
                            @click="
                                form.delete_image_file = false;
                                form.image_file = null;
                            "
                        >
                            Cancel deletion
                        </Button>
                    </div>
                </div>
            </div>
            <DialogFooter>
                <Button type="submit" :disabled="form.processing"> Update Podcast </Button>
            </DialogFooter>
        </form>
    </Modal>
</template>
