<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import { useForm } from '@inertiajs/vue3';
import { MoreHorizontal, Plus } from 'lucide-vue-next';
import { ref } from 'vue';
import {
    store,
    destroy,
    show,
    update,
} from '@/actions/App/Http/Controllers/FeedController';
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
import { Textarea } from '@/components/ui/textarea';
import AppLayout from '@/layouts/AppLayout.vue';
import { dashboard } from '@/routes';
import type { BreadcrumbItem } from '@/types';

defineProps<{
    feeds: any[];
}>();

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Dashboard',
        href: dashboard(),
    },
];

const form = useForm({
    title: '',
    description: '',
});

const isDialogOpen = ref(false);

const submit = () => {
    form.post(store.url(), {
        onSuccess: () => {
            form.reset();
            isDialogOpen.value = false;
        },
    });
};

const deleteFeed = (id: number) => {
    if (confirm('Are you sure you want to delete this feed?')) {
        useForm({}).delete(destroy.url(id));
    }
};

const isEditDialogOpen = ref(false);
const editingFeed = ref<any>(null);
const editFeedForm = useForm({
    title: '',
    description: '',
});

const startEditFeed = (feed: any) => {
    editingFeed.value = feed;
    editFeedForm.title = feed.title;
    editFeedForm.description = feed.description ?? '';
    isEditDialogOpen.value = true;
};

const submitEditFeed = () => {
    editFeedForm.put(update.url(editingFeed.value.id), {
        onSuccess: () => {
            editingFeed.value = null;
            isEditDialogOpen.value = false;
        },
    });
};
</script>

<template>
    <Head title="Dashboard" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div
            class="mx-auto flex h-full w-full max-w-5xl flex-1 flex-col gap-6 overflow-x-auto rounded-xl p-4"
        >
            <div class="flex items-center justify-between">
                <h2 class="text-2xl font-bold tracking-tight">Feeds</h2>
                <Dialog v-model:open="isDialogOpen">
                    <DialogTrigger as-child>
                        <Button>
                            <Plus class="mr-2 h-4 w-4" />
                            New Feed
                        </Button>
                    </DialogTrigger>
                    <DialogContent>
                        <form @submit.prevent="submit">
                            <DialogHeader>
                                <DialogTitle>Create New Feed</DialogTitle>
                                <DialogDescription>
                                    Add a new feed to start tracking entries.
                                </DialogDescription>
                            </DialogHeader>
                            <div class="grid gap-4 py-4">
                                <div class="grid gap-2">
                                    <Label for="title">Title</Label>
                                    <Input
                                        id="title"
                                        v-model="form.title"
                                        placeholder="Enter feed title..."
                                        required
                                    />
                                    <div
                                        v-if="form.errors.title"
                                        class="text-sm text-red-500"
                                    >
                                        {{ form.errors.title }}
                                    </div>
                                </div>
                                <div class="grid gap-2">
                                    <Label for="description">Description</Label>
                                    <Textarea
                                        id="description"
                                        v-model="form.description"
                                        placeholder="Optional description for this podcast feed..."
                                        rows="3"
                                    />
                                    <div
                                        v-if="form.errors.description"
                                        class="text-sm text-red-500"
                                    >
                                        {{ form.errors.description }}
                                    </div>
                                </div>
                            </div>
                            <DialogFooter>
                                <Button
                                    type="submit"
                                    :disabled="form.processing"
                                >
                                    Create Feed
                                </Button>
                            </DialogFooter>
                        </form>
                    </DialogContent>
                </Dialog>
            </div>

            <Dialog v-model:open="isEditDialogOpen">
                <DialogContent>
                    <form @submit.prevent="submitEditFeed">
                        <DialogHeader>
                            <DialogTitle>Edit Feed</DialogTitle>
                            <DialogDescription>
                                Update the details of this feed.
                            </DialogDescription>
                        </DialogHeader>
                        <div class="grid gap-4 py-4">
                            <div class="grid gap-2">
                                <Label for="edit-title">Title</Label>
                                <Input
                                    id="edit-title"
                                    v-model="editFeedForm.title"
                                    required
                                />
                                <div
                                    v-if="editFeedForm.errors.title"
                                    class="text-sm text-red-500"
                                >
                                    {{ editFeedForm.errors.title }}
                                </div>
                            </div>
                            <div class="grid gap-2">
                                <Label for="edit-description"
                                    >Description</Label
                                >
                                <Textarea
                                    id="edit-description"
                                    v-model="editFeedForm.description"
                                    placeholder="Optional description for this podcast feed..."
                                    rows="3"
                                />
                                <div
                                    v-if="editFeedForm.errors.description"
                                    class="text-sm text-red-500"
                                >
                                    {{ editFeedForm.errors.description }}
                                </div>
                            </div>
                        </div>
                        <DialogFooter>
                            <Button
                                type="submit"
                                :disabled="editFeedForm.processing"
                            >
                                Update Feed
                            </Button>
                        </DialogFooter>
                    </form>
                </DialogContent>
            </Dialog>

            <div class="rounded-md border bg-white dark:bg-zinc-950">
                <Table>
                    <TableHeader>
                        <TableRow>
                            <TableHead class="w-[50%]">Title</TableHead>
                            <TableHead>Entries</TableHead>
                            <TableHead class="text-right">Actions</TableHead>
                        </TableRow>
                    </TableHeader>
                    <TableBody>
                        <TableRow v-for="feed in feeds" :key="feed.id">
                            <TableCell class="font-medium">
                                <Link
                                    :href="show.url(feed.id)"
                                    class="hover:underline"
                                >
                                    {{ feed.title }}
                                </Link>
                            </TableCell>
                            <TableCell>{{ feed.entries_count }}</TableCell>
                            <TableCell class="text-right">
                                <DropdownMenu>
                                    <DropdownMenuTrigger as-child>
                                        <Button
                                            variant="ghost"
                                            class="h-8 w-8 p-0"
                                        >
                                            <span class="sr-only"
                                                >Open menu</span
                                            >
                                            <MoreHorizontal class="h-4 w-4" />
                                        </Button>
                                    </DropdownMenuTrigger>
                                    <DropdownMenuContent align="end">
                                        <DropdownMenuItem
                                            @click="startEditFeed(feed)"
                                        >
                                            Edit
                                        </DropdownMenuItem>
                                        <DropdownMenuItem
                                            class="text-red-600"
                                            @click="deleteFeed(feed.id)"
                                        >
                                            Delete
                                        </DropdownMenuItem>
                                    </DropdownMenuContent>
                                </DropdownMenu>
                            </TableCell>
                        </TableRow>
                        <TableRow v-if="feeds.length === 0">
                            <TableCell colspan="3" class="h-24 text-center">
                                No feeds created yet.
                            </TableCell>
                        </TableRow>
                    </TableBody>
                </Table>
            </div>
        </div>
    </AppLayout>
</template>
