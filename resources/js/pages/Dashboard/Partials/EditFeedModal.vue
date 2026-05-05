<script setup lang="ts">
import { useForm } from '@inertiajs/vue3';
import Rss from '~icons/lucide/rss';
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
    sync_frequency: '0',
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
        form.sync_frequency = (feed.sync_frequency ?? 0).toString();

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
    <Modal title="Edit Feed" description="Update the details of this feed.">
        <form @submit.prevent="submit">
            <div class="grid gap-4 py-4">
                <div
                    v-if="feed?.rss_url"
                    class="space-y-4 rounded-none border-3 bg-yellow-50 p-4 text-sm text-yellow-700 dark:border-yellow-200/10 dark:bg-yellow-700/10 dark:text-yellow-100"
                >
                    <div class="flex items-center gap-2">
                        <Rss class="h-4 w-4" />
                        <span class="font-medium">External Feed</span>
                    </div>
                    <p>
                        Title, description, and image are automatically managed from the RSS feed.
                        Sync frequency can still be adjusted.
                    </p>
                </div>
                <div v-if="feed?.rss_url" class="grid gap-2">
                    <Label for="edit-rss-url">RSS URL</Label>
                    <div
                        id="edit-rss-url"
                        class="select-all rounded-none border-3 border-neo-dark bg-muted px-3 py-2 text-sm break-all text-muted-foreground"
                    >
                        {{ feed.rss_url }}
                    </div>
                </div>
                <div class="grid gap-2">
                    <Label for="edit-title">Title</Label>
                    <Input
                        id="edit-title"
                        v-model="form.title"
                        :readonly="!!feed?.rss_url"
                        :class="{
                            'bg-muted text-muted-foreground': !!feed?.rss_url,
                        }"
                        required
                    />
                    <div v-if="form.errors.title" class="text-sm text-red-500">
                        {{ form.errors.title }}
                    </div>
                </div>
                <div class="grid gap-2">
                    <Label for="edit-description">Description</Label>
                    <Textarea
                        id="edit-description"
                        v-model="form.description"
                        placeholder="Optional description for this podcast feed..."
                        rows="3"
                        :readonly="!!feed?.rss_url"
                        :class="{
                            'bg-muted text-muted-foreground': !!feed?.rss_url,
                        }"
                    />
                    <div v-if="form.errors.description" class="text-sm text-red-500">
                        {{ form.errors.description }}
                    </div>
                </div>
                <div v-if="feed?.rss_url" class="grid gap-2">
                    <Label for="edit-sync_frequency">Sync Frequency</Label>
                    <Select v-model="form.sync_frequency">
                        <SelectTrigger id="edit-sync_frequency">
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
                <template v-if="!feed?.rss_url">
                    <div class="grid gap-2">
                        <Label for="editFeedImageSource">Image Source</Label>
                        <Select v-model="imageSource">
                            <SelectTrigger>
                                <SelectValue placeholder="Select source" />
                            </SelectTrigger>
                            <SelectContent>
                                <SelectItem value="url">Remote URL</SelectItem>
                                <SelectItem v-if="canUploadFiles" value="file"
                                    >Upload File</SelectItem
                                >
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
                </template>
                <div v-else class="grid gap-2">
                    <Label>Feed Image</Label>
                    <div class="flex items-center gap-4">
                        <img
                            v-if="feed.absolute_image_url"
                            :src="feed.absolute_image_url"
                            alt=""
                            class="h-16 w-16 rounded-none border-3 border-neo-dark object-cover"
                        />
                        <div
                            v-else
                            class="flex h-16 w-16 items-center justify-center rounded-none border-3 border-neo-dark bg-muted"
                        >
                            <Rss class="h-8 w-8 text-muted" />
                        </div>
                        <div class="text-xs text-muted-foreground">Managed by RSS feed</div>
                    </div>
                </div>
            </div>
            <DialogFooter>
                <Button type="submit" :disabled="form.processing"> Update Feed </Button>
            </DialogFooter>
        </form>
    </Modal>
</template>
