<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import { useForm } from '@inertiajs/vue3';
import { MoreHorizontal, Plus, Rss } from 'lucide-vue-next';
import { ref } from 'vue';
import {
    store as storeEntry,
    destroy as destroyEntry,
    update as updateEntryAction,
    file as getEntryFile,
} from '@/actions/App/Http/Controllers/EntryController';
import FeedRssController from '@/actions/App/Http/Controllers/FeedRssController';
import { Button } from '@/components/ui/button';
import {
    Dialog,
    DialogContent,
    DialogDescription,
    DialogFooter,
    DialogHeader,
    DialogTitle,
    DialogTrigger,
} from '@/components/ui/dialog';
import {
    DropdownMenu,
    DropdownMenuContent,
    DropdownMenuItem,
    DropdownMenuTrigger,
} from '@/components/ui/dropdown-menu';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import {
    Table,
    TableBody,
    TableCell,
    TableHead,
    TableHeader,
    TableRow,
} from '@/components/ui/table';
import AppLayout from '@/layouts/AppLayout.vue';
import { dashboard } from '@/routes';
import type { BreadcrumbItem } from '@/types';

const props = defineProps<{
    feed: any;
}>();

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Dashboard',
        href: dashboard(),
    },
    {
        title: props.feed.title,
        href: '#',
    },
];

const entryForm = useForm({
    feed_id: props.feed.id,
    name: '',
    description: '',
    file: null as File | null,
});

const showEntryForm = ref(false);

const submitEntry = () => {
    entryForm.submit(storeEntry(), {
        onSuccess: () => {
            entryForm.reset('name', 'description', 'file');
            showEntryForm.value = false;
        },
    });
};

const deleteEntry = (id: number) => {
    if (confirm('Are you sure you want to delete this entry?')) {
        useForm({}).submit(destroyEntry(id));
    }
};

const isEditDialogOpen = ref(false);
const editingEntry = ref<any>(null);
const editEntryForm = useForm({
    name: '',
    description: '',
    file: null as File | null,
    delete_file: false,
});

const startEditEntry = (entry: any) => {
    editingEntry.value = entry;
    editEntryForm.name = entry.name;
    editEntryForm.description = entry.description;
    editEntryForm.file = null;
    editEntryForm.delete_file = false;
    isEditDialogOpen.value = true;
};

const submitEditEntry = () => {
    editEntryForm.submit(updateEntryAction(editingEntry.value.id), {
        onSuccess: () => {
            editingEntry.value = null;
            isEditDialogOpen.value = false;
        },
    });
};
</script>

<template>
    <Head :title="feed.title" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div
            class="mx-auto flex h-full w-full max-w-5xl flex-1 flex-col gap-6 overflow-x-auto rounded-xl p-4"
        >
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <h2 class="text-2xl font-bold tracking-tight">
                        {{ feed.title }}
                    </h2>
                    <a
                        :href="FeedRssController.url(feed.id)"
                        target="_blank"
                        class="text-orange-500 hover:text-orange-600"
                        title="RSS Feed"
                    >
                        <Rss class="h-5 w-5" />
                    </a>
                </div>

                <Dialog v-model:open="showEntryForm">
                    <DialogTrigger as-child>
                        <Button>
                            <Plus class="mr-2 h-4 w-4" />
                            Add Entry
                        </Button>
                    </DialogTrigger>
                    <DialogContent>
                        <form @submit.prevent="submitEntry">
                            <DialogHeader>
                                <DialogTitle>New Entry</DialogTitle>
                                <DialogDescription>
                                    Add a new entry to this feed.
                                </DialogDescription>
                            </DialogHeader>
                            <div class="grid gap-4 py-4">
                                <div class="grid gap-2">
                                    <Label for="name">Name</Label>
                                    <Input
                                        id="name"
                                        v-model="entryForm.name"
                                        placeholder="Entry Name"
                                        required
                                    />
                                    <div
                                        v-if="entryForm.errors.name"
                                        class="text-sm text-red-500"
                                    >
                                        {{ entryForm.errors.name }}
                                    </div>
                                </div>
                                <div class="grid gap-2">
                                    <Label for="description">Description</Label>
                                    <Input
                                        id="description"
                                        v-model="entryForm.description"
                                        placeholder="Optional description"
                                    />
                                    <div
                                        v-if="entryForm.errors.description"
                                        class="text-sm text-red-500"
                                    >
                                        {{ entryForm.errors.description }}
                                    </div>
                                </div>
                                <div class="grid gap-2">
                                    <Label for="file">Audio File</Label>
                                    <Input
                                        id="file"
                                        type="file"
                                        @input="
                                            entryForm.file =
                                                $event.target.files[0]
                                        "
                                        accept="audio/*"
                                    />
                                    <div
                                        v-if="entryForm.errors.file"
                                        class="text-sm text-red-500"
                                    >
                                        {{ entryForm.errors.file }}
                                    </div>
                                </div>
                            </div>
                            <DialogFooter>
                                <Button
                                    type="submit"
                                    :disabled="entryForm.processing"
                                >
                                    Save Entry
                                </Button>
                            </DialogFooter>
                        </form>
                    </DialogContent>
                </Dialog>
            </div>

            <Dialog v-model:open="isEditDialogOpen">
                <DialogContent>
                    <form @submit.prevent="submitEditEntry">
                        <DialogHeader>
                            <DialogTitle>Edit Entry</DialogTitle>
                            <DialogDescription>
                                Update the details of this entry.
                            </DialogDescription>
                        </DialogHeader>
                        <div class="grid gap-4 py-4">
                            <div class="grid gap-2">
                                <Label for="edit-name">Name</Label>
                                <Input
                                    id="edit-name"
                                    v-model="editEntryForm.name"
                                    required
                                />
                                <div
                                    v-if="editEntryForm.errors.name"
                                    class="text-sm text-red-500"
                                >
                                    {{ editEntryForm.errors.name }}
                                </div>
                            </div>
                            <div class="grid gap-2">
                                <Label for="edit-description"
                                    >Description</Label
                                >
                                <Input
                                    id="edit-description"
                                    v-model="editEntryForm.description"
                                />
                                <div
                                    v-if="editEntryForm.errors.description"
                                    class="text-sm text-red-500"
                                >
                                    {{ editEntryForm.errors.description }}
                                </div>
                            </div>
                            <div class="grid gap-2">
                                <Label for="edit-file">Audio File</Label>
                                <div
                                    v-if="
                                        editingEntry?.file_path &&
                                        !editEntryForm.delete_file &&
                                        !editEntryForm.file
                                    "
                                    class="flex items-center gap-4"
                                >
                                    <span class="text-sm text-gray-500"
                                        >Current file attached</span
                                    >
                                    <Button
                                        type="button"
                                        variant="destructive"
                                        size="sm"
                                        @click="
                                            editEntryForm.delete_file = true
                                        "
                                    >
                                        Delete
                                    </Button>
                                </div>
                                <div v-else>
                                    <Input
                                        id="edit-file"
                                        type="file"
                                        @input="
                                            editEntryForm.file =
                                                $event.target.files[0]
                                        "
                                        accept="audio/*"
                                    />
                                    <div
                                        v-if="editEntryForm.errors.file"
                                        class="text-sm text-red-500"
                                    >
                                        {{ editEntryForm.errors.file }}
                                    </div>
                                    <Button
                                        v-if="
                                            editEntryForm.delete_file &&
                                            editingEntry?.file_path
                                        "
                                        type="button"
                                        variant="link"
                                        size="sm"
                                        class="mt-1 px-0 text-gray-500"
                                        @click="
                                            editEntryForm.delete_file = false;
                                            editEntryForm.file = null;
                                        "
                                    >
                                        Cancel deletion
                                    </Button>
                                </div>
                            </div>
                        </div>
                        <DialogFooter>
                            <Button
                                type="submit"
                                :disabled="editEntryForm.processing"
                            >
                                Update Entry
                            </Button>
                        </DialogFooter>
                    </form>
                </DialogContent>
            </Dialog>

            <div class="rounded-md border bg-white dark:bg-zinc-950">
                <Table>
                    <TableHeader>
                        <TableRow>
                            <TableHead class="w-[55%]">Name</TableHead>
                            <TableHead class="w-[20%]">File</TableHead>
                            <TableHead class="text-right">Actions</TableHead>
                        </TableRow>
                    </TableHeader>
                    <TableBody>
                        <template v-for="entry in feed.entries" :key="entry.id">
                            <TableRow>
                                <TableCell class="align-top font-medium">{{
                                    entry.name
                                }}</TableCell>
                                <TableCell class="align-top">
                                    <a
                                        v-if="entry.file_path"
                                        :href="getEntryFile.url(entry.id)"
                                        target="_blank"
                                        class="text-blue-600 hover:underline dark:text-blue-400"
                                    >
                                        Download
                                    </a>
                                    <span
                                        v-else
                                        class="text-gray-400 dark:text-gray-600"
                                        >-</span
                                    >
                                </TableCell>
                                <TableCell class="text-right align-top">
                                    <DropdownMenu>
                                        <DropdownMenuTrigger as-child>
                                            <Button
                                                variant="ghost"
                                                class="h-8 w-8 p-0"
                                            >
                                                <span class="sr-only"
                                                    >Open menu</span
                                                >
                                                <MoreHorizontal
                                                    class="h-4 w-4"
                                                />
                                            </Button>
                                        </DropdownMenuTrigger>
                                        <DropdownMenuContent align="end">
                                            <DropdownMenuItem
                                                @click="startEditEntry(entry)"
                                            >
                                                Edit
                                            </DropdownMenuItem>
                                            <DropdownMenuItem
                                                class="text-red-600"
                                                @click="deleteEntry(entry.id)"
                                            >
                                                Delete
                                            </DropdownMenuItem>
                                        </DropdownMenuContent>
                                    </DropdownMenu>
                                </TableCell>
                            </TableRow>
                        </template>
                        <TableRow v-if="feed.entries.length === 0">
                            <TableCell colspan="3" class="h-24 text-center">
                                No entries yet. Click "Add Entry" to create one.
                            </TableCell>
                        </TableRow>
                    </TableBody>
                </Table>
            </div>
        </div>
    </AppLayout>
</template>
