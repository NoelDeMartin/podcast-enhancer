<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import { useForm } from '@inertiajs/vue3';
import { MoreHorizontal, Plus, Rss, Loader2 } from 'lucide-vue-next';
import { ref } from 'vue';
import {
    store,
    destroy,
    show,
    update,
} from '@/actions/App/Http/Controllers/FeedController';
import { store as syncStore } from '@/actions/App/Http/Controllers/FeedSyncController';
import { Button } from '@/components/ui/button';
import {
    Dialog,
    DialogClose,
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
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';
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
    image_url: '',
    image_file: null as File | null,
});

const feedImageSource = ref<'url' | 'file'>('url');

const isDialogOpen = ref(false);

const submit = () => {
    form.post(store.url(), {
        onSuccess: () => {
            form.reset();
            isDialogOpen.value = false;
        },
    });
};

const isDeleteDialogOpen = ref(false);
const deletingFeed = ref<any>(null);
const deleteFeedForm = useForm({});

const startDeleteFeed = (feed: any) => {
    deletingFeed.value = feed;
    isDeleteDialogOpen.value = true;
};

const cancelDeleteFeed = () => {
    isDeleteDialogOpen.value = false;
    deletingFeed.value = null;
};

const submitDeleteFeed = () => {
    if (!deletingFeed.value) {
        return;
    }

    deleteFeedForm.delete(destroy.url(deletingFeed.value.id), {
        onFinish: () => {
            isDeleteDialogOpen.value = false;
            deletingFeed.value = null;
        },
    });
};

const isEditDialogOpen = ref(false);
const editingFeed = ref<any>(null);
const editFeedForm = useForm({
    title: '',
    description: '',
    image_url: '',
    image_file: null as File | null,
    delete_image_file: false,
    sync_frequency: '0',
});

const editFeedImageSource = ref<'url' | 'file'>('url');

const isExternal = (url: string | null) => {
    if (!url) {
        return false;
    }

    return url.startsWith('http://') || url.startsWith('https://');
};

const startEditFeed = (feed: any) => {
    editingFeed.value = feed;
    editFeedForm.title = feed.title;
    editFeedForm.description = feed.description ?? '';
    editFeedForm.sync_frequency = (feed.sync_frequency ?? 0).toString();

    const external = isExternal(feed.image_url);
    editFeedImageSource.value = external ? 'url' : 'file';
    editFeedForm.image_url = external ? feed.image_url : '';
    editFeedForm.image_file = null;
    editFeedForm.delete_image_file = false;

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

const isImportRssDialogOpen = ref(false);
const importRssForm = useForm({
    rss_url: '',
    title: '',
    description: '',
    sync_frequency: '0',
});

const submitImportRss = () => {
    importRssForm.post(syncStore.url(), {
        onSuccess: () => {
            importRssForm.reset();
            isImportRssDialogOpen.value = false;
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
                <div class="flex gap-2">
                    <Dialog v-model:open="isImportRssDialogOpen">
                        <DialogTrigger as-child>
                            <Button variant="outline">
                                <Rss class="mr-2 h-4 w-4" />
                                Import from RSS
                            </Button>
                        </DialogTrigger>
                        <DialogContent>
                            <form @submit.prevent="submitImportRss">
                                <DialogHeader>
                                    <DialogTitle
                                        >Import Feed from RSS</DialogTitle
                                    >
                                    <DialogDescription>
                                        Create a synchronized feed automatically
                                        from an RSS URL.
                                    </DialogDescription>
                                </DialogHeader>
                                <div class="grid gap-4 py-4">
                                    <div class="grid gap-2">
                                        <Label for="rss_url"
                                            >RSS URL
                                            <span class="text-red-500"
                                                >*</span
                                            ></Label
                                        >
                                        <Input
                                            id="rss_url"
                                            v-model="importRssForm.rss_url"
                                            placeholder="https://example.com/feed.xml"
                                            required
                                        />
                                        <div
                                            v-if="importRssForm.errors.rss_url"
                                            class="text-sm text-red-500"
                                        >
                                            {{ importRssForm.errors.rss_url }}
                                        </div>
                                    </div>
                                    <div class="grid gap-2">
                                        <Label for="import-title">Title</Label>
                                        <Input
                                            id="import-title"
                                            v-model="importRssForm.title"
                                            placeholder="Leave empty to use feed title"
                                        />
                                        <div
                                            v-if="importRssForm.errors.title"
                                            class="text-sm text-red-500"
                                        >
                                            {{ importRssForm.errors.title }}
                                        </div>
                                    </div>
                                    <div class="grid gap-2">
                                        <Label for="import-description"
                                            >Description</Label
                                        >
                                        <Textarea
                                            id="import-description"
                                            v-model="importRssForm.description"
                                            placeholder="Leave empty to use feed description"
                                            rows="3"
                                        />
                                        <div
                                            v-if="
                                                importRssForm.errors.description
                                            "
                                            class="text-sm text-red-500"
                                        >
                                            {{
                                                importRssForm.errors.description
                                            }}
                                        </div>
                                    </div>
                                    <div class="grid gap-2">
                                        <Label for="sync_frequency"
                                            >Sync Frequency</Label
                                        >
                                        <Select
                                            v-model="
                                                importRssForm.sync_frequency
                                            "
                                        >
                                            <SelectTrigger id="sync_frequency">
                                                <SelectValue
                                                    placeholder="Select frequency"
                                                />
                                            </SelectTrigger>
                                            <SelectContent>
                                                <SelectItem value="0"
                                                    >Manual only</SelectItem
                                                >
                                                <SelectItem value="3600"
                                                    >Every hour</SelectItem
                                                >
                                                <SelectItem value="21600"
                                                    >Every 6 hours</SelectItem
                                                >
                                                <SelectItem value="43200"
                                                    >Every 12 hours</SelectItem
                                                >
                                                <SelectItem value="86400"
                                                    >Daily</SelectItem
                                                >
                                                <SelectItem value="604800"
                                                    >Weekly</SelectItem
                                                >
                                            </SelectContent>
                                        </Select>
                                        <div
                                            v-if="
                                                importRssForm.errors
                                                    .sync_frequency
                                            "
                                            class="text-sm text-red-500"
                                        >
                                            {{
                                                importRssForm.errors
                                                    .sync_frequency
                                            }}
                                        </div>
                                    </div>
                                    <div class="text-xs text-muted-foreground">
                                        Note: Synchronized feeds will not
                                        automatically transcribe imported
                                        episodes to reduce server load.
                                    </div>
                                </div>
                                <DialogFooter>
                                    <Button
                                        type="submit"
                                        :disabled="importRssForm.processing"
                                    >
                                        <Loader2
                                            v-if="importRssForm.processing"
                                            class="mr-2 h-4 w-4 animate-spin"
                                        />
                                        Import Feed
                                    </Button>
                                </DialogFooter>
                            </form>
                        </DialogContent>
                    </Dialog>
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
                                        Add a new feed to start tracking
                                        entries.
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
                                        <Label for="description"
                                            >Description</Label
                                        >
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
                                    <div class="grid gap-2">
                                        <Label for="feedImageSource"
                                            >Image Source</Label
                                        >
                                        <Select v-model="feedImageSource">
                                            <SelectTrigger>
                                                <SelectValue
                                                    placeholder="Select source"
                                                />
                                            </SelectTrigger>
                                            <SelectContent>
                                                <SelectItem value="url"
                                                    >Remote URL</SelectItem
                                                >
                                                <SelectItem value="file"
                                                    >Upload File</SelectItem
                                                >
                                            </SelectContent>
                                        </Select>
                                    </div>
                                    <div
                                        v-if="feedImageSource === 'url'"
                                        class="grid gap-2"
                                    >
                                        <Label for="image_url">Image URL</Label>
                                        <Input
                                            id="image_url"
                                            v-model="form.image_url"
                                            placeholder="https://example.com/image.jpg"
                                        />
                                        <div
                                            v-if="form.errors.image_url"
                                            class="text-sm text-red-500"
                                        >
                                            {{ form.errors.image_url }}
                                        </div>
                                    </div>
                                    <div
                                        v-if="feedImageSource === 'file'"
                                        class="grid gap-2"
                                    >
                                        <Label for="image_file"
                                            >Image File</Label
                                        >
                                        <Input
                                            id="image_file"
                                            type="file"
                                            @input="
                                                form.image_file =
                                                    $event.target.files[0]
                                            "
                                            accept="image/*"
                                        />
                                        <div
                                            v-if="form.errors.image_file"
                                            class="text-sm text-red-500"
                                        >
                                            {{ form.errors.image_file }}
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
                            <div v-if="editingFeed?.rss_url" class="grid gap-2">
                                <Label for="edit-rss-url">RSS URL</Label>
                                <div
                                    id="edit-rss-url"
                                    class="rounded-md border bg-muted px-3 py-2 text-sm break-all text-muted-foreground select-all"
                                >
                                    {{ editingFeed.rss_url }}
                                </div>
                                <div class="text-xs text-muted-foreground">
                                    The RSS URL cannot be changed.
                                </div>
                            </div>
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
                            <div v-if="editingFeed?.rss_url" class="grid gap-2">
                                <Label for="edit-sync_frequency"
                                    >Sync Frequency</Label
                                >
                                <Select v-model="editFeedForm.sync_frequency">
                                    <SelectTrigger id="edit-sync_frequency">
                                        <SelectValue
                                            placeholder="Select frequency"
                                        />
                                    </SelectTrigger>
                                    <SelectContent>
                                        <SelectItem value="0"
                                            >Manual only</SelectItem
                                        >
                                        <SelectItem value="3600"
                                            >Every hour</SelectItem
                                        >
                                        <SelectItem value="21600"
                                            >Every 6 hours</SelectItem
                                        >
                                        <SelectItem value="43200"
                                            >Every 12 hours</SelectItem
                                        >
                                        <SelectItem value="86400"
                                            >Daily</SelectItem
                                        >
                                        <SelectItem value="604800"
                                            >Weekly</SelectItem
                                        >
                                    </SelectContent>
                                </Select>
                                <div
                                    v-if="editFeedForm.errors.sync_frequency"
                                    class="text-sm text-red-500"
                                >
                                    {{ editFeedForm.errors.sync_frequency }}
                                </div>
                            </div>
                            <div class="grid gap-2">
                                <Label for="editFeedImageSource"
                                    >Image Source</Label
                                >
                                <Select v-model="editFeedImageSource">
                                    <SelectTrigger>
                                        <SelectValue
                                            placeholder="Select source"
                                        />
                                    </SelectTrigger>
                                    <SelectContent>
                                        <SelectItem value="url"
                                            >Remote URL</SelectItem
                                        >
                                        <SelectItem value="file"
                                            >Upload File</SelectItem
                                        >
                                    </SelectContent>
                                </Select>
                            </div>
                            <div
                                v-if="editFeedImageSource === 'url'"
                                class="grid gap-2"
                            >
                                <Label for="edit-image_url">Image URL</Label>
                                <Input
                                    id="edit-image_url"
                                    v-model="editFeedForm.image_url"
                                    placeholder="https://example.com/image.jpg"
                                />
                                <div
                                    v-if="editFeedForm.errors.image_url"
                                    class="text-sm text-red-500"
                                >
                                    {{ editFeedForm.errors.image_url }}
                                </div>
                            </div>
                            <div
                                v-if="editFeedImageSource === 'file'"
                                class="grid gap-2"
                            >
                                <Label for="edit-image_file">Image File</Label>
                                <div
                                    v-if="
                                        !isExternal(editingFeed?.image_url) &&
                                        editingFeed?.image_url &&
                                        !editFeedForm.delete_image_file &&
                                        !editFeedForm.image_file
                                    "
                                    class="flex items-center gap-4"
                                >
                                    <span class="text-sm text-gray-500"
                                        >Current image attached</span
                                    >
                                    <Button
                                        type="button"
                                        variant="destructive"
                                        size="sm"
                                        @click="
                                            editFeedForm.delete_image_file = true
                                        "
                                    >
                                        Delete
                                    </Button>
                                </div>
                                <div v-else>
                                    <Input
                                        id="edit-image_file"
                                        type="file"
                                        @input="
                                            editFeedForm.image_file =
                                                $event.target.files[0]
                                        "
                                        accept="image/*"
                                    />
                                    <div
                                        v-if="editFeedForm.errors.image_file"
                                        class="text-sm text-red-500"
                                    >
                                        {{ editFeedForm.errors.image_file }}
                                    </div>
                                    <Button
                                        v-if="
                                            editFeedForm.delete_image_file &&
                                            !isExternal(
                                                editingFeed?.image_url,
                                            ) &&
                                            editingFeed?.image_url
                                        "
                                        type="button"
                                        variant="link"
                                        size="sm"
                                        class="mt-1 px-0 text-gray-500"
                                        @click="
                                            editFeedForm.delete_image_file = false;
                                            editFeedForm.image_file = null;
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
                                :disabled="editFeedForm.processing"
                            >
                                Update Feed
                            </Button>
                        </DialogFooter>
                    </form>
                </DialogContent>
            </Dialog>

            <Dialog v-model:open="isDeleteDialogOpen">
                <DialogContent>
                    <form @submit.prevent="submitDeleteFeed" class="space-y-6">
                        <DialogHeader class="space-y-3">
                            <DialogTitle>Delete feed</DialogTitle>
                            <DialogDescription>
                                This is a dangerous operation and cannot be
                                undone.
                            </DialogDescription>
                        </DialogHeader>

                        <div
                            class="space-y-3 rounded-lg border border-red-100 bg-red-50 p-4 text-sm text-red-700 dark:border-red-200/10 dark:bg-red-700/10 dark:text-red-100"
                        >
                            <div class="space-y-1">
                                <div class="font-medium">
                                    You are about to permanently delete:
                                </div>
                                <div class="font-semibold">
                                    {{ deletingFeed?.title ?? 'This feed' }}
                                </div>
                            </div>
                            <div>
                                This will also permanently delete
                                <span class="font-semibold">
                                    {{ deletingFeed?.entries_count ?? 0 }}
                                </span>
                                {{
                                    (deletingFeed?.entries_count ?? 0) === 1
                                        ? 'entry'
                                        : 'entries'
                                }}.
                            </div>
                        </div>

                        <DialogFooter class="gap-2">
                            <DialogClose as-child>
                                <Button
                                    type="button"
                                    variant="secondary"
                                    :disabled="deleteFeedForm.processing"
                                    @click="cancelDeleteFeed"
                                >
                                    Cancel
                                </Button>
                            </DialogClose>
                            <Button
                                type="submit"
                                variant="destructive"
                                :disabled="deleteFeedForm.processing"
                            >
                                Delete feed
                            </Button>
                        </DialogFooter>
                    </form>
                </DialogContent>
            </Dialog>

            <div class="rounded-md border bg-white dark:bg-zinc-950">
                <Table>
                    <TableHeader>
                        <TableRow>
                            <TableHead class="w-[10%]">Image</TableHead>
                            <TableHead class="w-[40%]">Title</TableHead>
                            <TableHead>Entries</TableHead>
                            <TableHead class="text-right">Actions</TableHead>
                        </TableRow>
                    </TableHeader>
                    <TableBody>
                        <TableRow v-for="feed in feeds" :key="feed.id">
                            <TableCell>
                                <img
                                    v-if="feed.image_url"
                                    :src="
                                        isExternal(feed.image_url)
                                            ? feed.image_url
                                            : `/storage/${feed.image_url}`
                                    "
                                    alt="Feed image"
                                    class="h-10 w-10 rounded object-cover"
                                />
                                <div
                                    v-else
                                    class="flex h-10 w-10 items-center justify-center rounded bg-gray-100 dark:bg-zinc-800"
                                >
                                    <Rss class="h-5 w-5 text-gray-400" />
                                </div>
                            </TableCell>
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
                                            @click="startDeleteFeed(feed)"
                                        >
                                            Delete
                                        </DropdownMenuItem>
                                    </DropdownMenuContent>
                                </DropdownMenu>
                            </TableCell>
                        </TableRow>
                        <TableRow v-if="feeds.length === 0">
                            <TableCell colspan="4" class="h-24 text-center">
                                No feeds created yet.
                            </TableCell>
                        </TableRow>
                    </TableBody>
                </Table>
            </div>
        </div>
    </AppLayout>
</template>
