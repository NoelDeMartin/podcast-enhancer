<script setup lang="ts">
import { useForm } from '@inertiajs/vue3';
import { ref } from 'vue';

import { store } from '@/actions/App/Http/Controllers/FeedController';
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

defineProps<{
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
});

const imageSource = ref<'url' | 'file'>('url');

const submit = () => {
    form.post(store.url(), {
        onSuccess: () => {
            form.reset();
            emit('close');
        },
    });
};
</script>

<template>
    <Modal title="Create New Feed" description="Add a new feed to start tracking entries.">
        <form @submit.prevent="submit">
            <div class="grid gap-4 py-4">
                <div class="grid gap-2">
                    <Label for="title">Title</Label>
                    <Input
                        id="title"
                        v-model="form.title"
                        placeholder="Enter feed title..."
                        required
                    />
                    <InputError :message="form.errors.title" />
                </div>
                <div class="grid gap-2">
                    <Label for="description">Description</Label>
                    <Textarea
                        id="description"
                        v-model="form.description"
                        placeholder="Optional description for this podcast feed..."
                        rows="3"
                    />
                    <InputError :message="form.errors.description" />
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
                        v-model="form.image_url"
                        placeholder="https://example.com/image.jpg"
                    />
                    <InputError :message="form.errors.image_url" />
                </div>
                <div v-if="imageSource === 'file'" class="grid gap-2">
                    <Label for="image_file">Image File</Label>
                    <Input
                        id="image_file"
                        type="file"
                        @input="form.image_file = $event.target.files[0]"
                        accept="image/*"
                    />
                    <InputError :message="form.errors.image_file" />
                </div>
            </div>
            <DialogFooter>
                <Button type="submit" :disabled="form.processing"> Create Feed </Button>
            </DialogFooter>
        </form>
    </Modal>
</template>
