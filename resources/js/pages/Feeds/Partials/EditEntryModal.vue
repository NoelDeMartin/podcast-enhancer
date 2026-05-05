<script setup lang="ts">
import { useForm } from '@inertiajs/vue3';
import { ref } from 'vue';

import { update as updateEntryAction } from '@/actions/App/Http/Controllers/EntryController';
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

const props = defineProps<{
    feed: any;
    entry: any;
    canUploadFiles?: boolean;
}>();

const emit = defineEmits<{
    close: [];
}>();

const form = useForm({
    name: props.entry.name,
    audio_url: '',
    file: null as File | null,
    delete_file: false,
    image_url: '',
    image_file: null as File | null,
    delete_image_file: false,
});

const isExternal = (url: string | null) => {
    if (!url) {
        return false;
    }

    return url.startsWith('http://') || url.startsWith('https://');
};

const external = isExternal(props.entry.audio_url);
const entrySource = ref<'url' | 'file'>(external ? 'url' : 'file');
form.audio_url = external ? props.entry.audio_url : '';

const externalImage = isExternal(props.entry.image_url);
const entryImageSource = ref<'url' | 'file'>(externalImage ? 'url' : 'file');
form.image_url = externalImage ? props.entry.image_url : '';

const submit = () => {
    form.put(updateEntryAction([props.feed, props.entry.slug]).url, {
        onSuccess: () => {
            emit('close');
        },
    });
};
</script>

<template>
    <Modal title="Edit Entry" description="Update the details of this entry.">
        <form @submit.prevent="submit">
            <div class="grid gap-4 py-4">
                <div class="grid gap-2">
                    <Label for="edit-name">Name</Label>
                    <Input id="edit-name" v-model="form.name" required />
                    <div v-if="form.errors.name" class="text-sm text-red-500">
                        {{ form.errors.name }}
                    </div>
                </div>
                <div class="grid gap-2">
                    <Label for="edit-source">Source Type</Label>
                    <Select v-model="entrySource">
                        <SelectTrigger>
                            <SelectValue placeholder="Select source" />
                        </SelectTrigger>
                        <SelectContent>
                            <SelectItem value="url">Remote URL</SelectItem>
                            <SelectItem v-if="canUploadFiles" value="file">Upload File</SelectItem>
                        </SelectContent>
                    </Select>
                </div>
                <div v-if="entrySource === 'url'" class="grid gap-2">
                    <Label for="edit-audio_url">Audio URL</Label>
                    <Input
                        id="edit-audio_url"
                        v-model="form.audio_url"
                        placeholder="https://example.com/audio.mp3"
                    />
                    <div v-if="form.errors.audio_url" class="text-sm text-red-500">
                        {{ form.errors.audio_url }}
                    </div>
                </div>
                <div v-if="entrySource === 'file'" class="grid gap-2">
                    <Label for="edit-file">Audio File</Label>
                    <div
                        v-if="
                            !isExternal(entry?.audio_url) &&
                            entry?.audio_url &&
                            !form.delete_file &&
                            !form.file
                        "
                        class="flex items-center gap-4"
                    >
                        <span class="text-sm text-gray-500">Current file attached</span>
                        <Button
                            type="button"
                            variant="destructive"
                            size="sm"
                            @click="form.delete_file = true"
                        >
                            Delete
                        </Button>
                    </div>
                    <div v-else>
                        <Input
                            id="edit-file"
                            type="file"
                            @input="form.file = $event.target.files[0]"
                            accept="audio/*"
                        />
                        <div v-if="form.errors.file" class="text-sm text-red-500">
                            {{ form.errors.file }}
                        </div>
                        <Button
                            v-if="
                                form.delete_file &&
                                !isExternal(entry?.audio_url) &&
                                entry?.audio_url
                            "
                            type="button"
                            variant="link"
                            size="sm"
                            class="mt-1 px-0 text-gray-500"
                            @click="
                                form.delete_file = false;
                                form.file = null;
                            "
                        >
                            Cancel deletion
                        </Button>
                    </div>
                </div>
                <div class="grid gap-2">
                    <Label for="editEntryImageSource">Image Source</Label>
                    <Select v-model="entryImageSource">
                        <SelectTrigger>
                            <SelectValue placeholder="Select source" />
                        </SelectTrigger>
                        <SelectContent>
                            <SelectItem value="url">Remote URL</SelectItem>
                            <SelectItem v-if="canUploadFiles" value="file">Upload File</SelectItem>
                        </SelectContent>
                    </Select>
                </div>
                <div v-if="entryImageSource === 'url'" class="grid gap-2">
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
                <div v-if="entryImageSource === 'file'" class="grid gap-2">
                    <Label for="edit-image_file">Image File</Label>
                    <div
                        v-if="
                            !isExternal(entry?.image_url) &&
                            entry?.image_url &&
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
                                !isExternal(entry?.image_url) &&
                                entry?.image_url
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
                <Button type="submit" :disabled="form.processing"> Update Entry </Button>
            </DialogFooter>
        </form>
    </Modal>
</template>
