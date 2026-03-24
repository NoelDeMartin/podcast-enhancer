<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import { useForm } from '@inertiajs/vue3';
import { MoreHorizontal, Plus } from 'lucide-vue-next';
import { ref } from 'vue';
import {
    store as storeEntry,
    destroy as destroyEntry,
    update as updateEntryAction,
} from '@/actions/App/Http/Controllers/EntryController';
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
});

const showEntryForm = ref(false);

const submitEntry = () => {
    entryForm.post(storeEntry.url(), {
        onSuccess: () => {
            entryForm.reset('name', 'description');
            showEntryForm.value = false;
        },
    });
};

const deleteEntry = (id: number) => {
    if (confirm('Are you sure you want to delete this entry?')) {
        useForm({}).delete(destroyEntry.url(id));
    }
};

const isEditDialogOpen = ref(false);
const editingEntry = ref<any>(null);
const editEntryForm = useForm({
    name: '',
    description: '',
});

const startEditEntry = (entry: any) => {
    editingEntry.value = entry;
    editEntryForm.name = entry.name;
    editEntryForm.description = entry.description;
    isEditDialogOpen.value = true;
};

const submitEditEntry = () => {
    editEntryForm.put(updateEntryAction.url(editingEntry.value.id), {
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
                <h2 class="text-2xl font-bold tracking-tight">Entries</h2>

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
                            <TableHead class="w-[30%]">Name</TableHead>
                            <TableHead class="w-[50%]">Description</TableHead>
                            <TableHead class="text-right">Actions</TableHead>
                        </TableRow>
                    </TableHeader>
                    <TableBody>
                        <template v-for="entry in feed.entries" :key="entry.id">
                            <TableRow>
                                <TableCell class="align-top font-medium">{{
                                    entry.name
                                }}</TableCell>
                                <TableCell
                                    class="align-top text-gray-600 dark:text-gray-300"
                                >
                                    {{ entry.description || '-' }}
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
