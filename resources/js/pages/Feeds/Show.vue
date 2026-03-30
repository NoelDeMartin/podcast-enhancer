<script setup lang="ts">
import { Head, usePoll } from '@inertiajs/vue3';
import { useForm } from '@inertiajs/vue3';
import { Clock, Loader2, MoreHorizontal, Plus, Rss } from 'lucide-vue-next';
import { computed, ref, watch } from 'vue';
import {
    store as storeEntry,
    destroy as destroyEntry,
    update as updateEntryAction,
    file as getEntryFile,
    transcribe as transcribeEntry,
} from '@/actions/App/Http/Controllers/EntryController';
import FeedRssController from '@/actions/App/Http/Controllers/FeedRssController';
import { Badge } from '@/components/ui/badge';
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
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';
import { Separator } from '@/components/ui/separator';
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
    audio_url: '',
    file: null as File | null,
});

const entrySource = ref<'url' | 'file'>('url');

const showEntryForm = ref(false);

const submitEntry = () => {
    entryForm.submit(storeEntry(), {
        onSuccess: () => {
            entryForm.reset('name', 'audio_url', 'file');
            showEntryForm.value = false;
        },
    });
};

const deleteEntry = (id: number) => {
    if (confirm('Are you sure you want to delete this entry?')) {
        useForm({}).submit(destroyEntry(id));
    }
};

const regenerateTranscription = (id: number) => {
    useForm({}).submit(transcribeEntry(id));
};

const viewingEntry = ref<any>(null);
const viewingFailure = ref<any>(null);

function formatTimestamp(seconds: number): string {
    const h = Math.floor(seconds / 3600);
    const m = Math.floor((seconds % 3600) / 60);
    const s = Math.floor(seconds % 60);

    return h > 0
        ? `${h}:${String(m).padStart(2, '0')}:${String(s).padStart(2, '0')}`
        : `${m}:${String(s).padStart(2, '0')}`;
}

function parsedTranscription(entry: any): any[] | null {
    if (!entry?.transcription) {
        return null;
    }

    try {
        return JSON.parse(entry.transcription);
    } catch {
        return null;
    }
}

function hasDetails(entry: any): boolean {
    return !!(entry.transcription || entry.summary || entry.chapters?.length);
}

type BatchStatus = 'pending' | 'failed' | 'completed' | null;

function getBatchStatus(entry: any): BatchStatus {
    const batch = entry.latest_job_batch?.job_batch;

    if (!batch) {
        return null;
    }

    if (batch.cancelled_at !== null) {
        return 'failed';
    }

    if (batch.finished_at !== null) {
        return 'completed';
    }

    return 'pending';
}

const hasActiveJobs = computed(() =>
    props.feed.entries.some((e: any) => getBatchStatus(e) === 'pending'),
);

const { start: startPolling, stop: stopPolling } = usePoll(
    3000,
    { only: ['feed'] },
    { autoStart: false },
);

watch(hasActiveJobs, (active) => (active ? startPolling() : stopPolling()), {
    immediate: true,
});

const isEditDialogOpen = ref(false);
const editingEntry = ref<any>(null);
const editEntryForm = useForm({
    name: '',
    audio_url: '',
    file: null as File | null,
    delete_file: false,
});

const editEntrySource = ref<'url' | 'file'>('url');

const isExternal = (url: string | null) => {
    if (!url) {
        return false;
    }

    return url.startsWith('http://') || url.startsWith('https://');
};

const startEditEntry = (entry: any) => {
    editingEntry.value = entry;
    editEntryForm.name = entry.name;
    const external = isExternal(entry.audio_url);
    editEntrySource.value = external ? 'url' : 'file';
    editEntryForm.audio_url = external ? entry.audio_url : '';
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
                                    <Label for="source">Source Type</Label>
                                    <Select v-model="entrySource">
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
                                    v-if="entrySource === 'url'"
                                    class="grid gap-2"
                                >
                                    <Label for="audio_url">Audio URL</Label>
                                    <Input
                                        id="audio_url"
                                        v-model="entryForm.audio_url"
                                        placeholder="https://example.com/audio.mp3"
                                    />
                                    <div
                                        v-if="entryForm.errors.audio_url"
                                        class="text-sm text-red-500"
                                    >
                                        {{ entryForm.errors.audio_url }}
                                    </div>
                                </div>
                                <div
                                    v-if="entrySource === 'file'"
                                    class="grid gap-2"
                                >
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
                                <Label for="edit-source">Source Type</Label>
                                <Select v-model="editEntrySource">
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
                                v-if="editEntrySource === 'url'"
                                class="grid gap-2"
                            >
                                <Label for="edit-audio_url">Audio URL</Label>
                                <Input
                                    id="edit-audio_url"
                                    v-model="editEntryForm.audio_url"
                                    placeholder="https://example.com/audio.mp3"
                                />
                                <div
                                    v-if="editEntryForm.errors.audio_url"
                                    class="text-sm text-red-500"
                                >
                                    {{ editEntryForm.errors.audio_url }}
                                </div>
                            </div>
                            <div
                                v-if="editEntrySource === 'file'"
                                class="grid gap-2"
                            >
                                <Label for="edit-file">Audio File</Label>
                                <div
                                    v-if="
                                        !isExternal(editingEntry?.audio_url) &&
                                        editingEntry?.audio_url &&
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
                                            !isExternal(
                                                editingEntry?.audio_url,
                                            ) &&
                                            editingEntry?.audio_url
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

            <Dialog
                :open="!!viewingFailure"
                @update:open="viewingFailure = null"
            >
                <DialogContent class="max-w-2xl">
                    <DialogHeader>
                        <DialogTitle>Transcription Failed</DialogTitle>
                        <DialogDescription>
                            {{ viewingFailure?.name }}
                        </DialogDescription>
                    </DialogHeader>
                    <div
                        class="max-h-[60vh] overflow-y-auto rounded-md border bg-red-50 p-4 dark:bg-red-950/20"
                    >
                        <pre
                            class="text-xs leading-relaxed whitespace-pre-wrap text-red-800 dark:text-red-300"
                            >{{
                                viewingFailure?.latest_job_batch?.job_batch
                                    ?.failed_job_details?.[0]?.exception ??
                                'No exception details available.'
                            }}</pre
                        >
                    </div>
                </DialogContent>
            </Dialog>

            <Dialog :open="!!viewingEntry" @update:open="viewingEntry = null">
                <DialogContent class="max-w-2xl">
                    <DialogHeader>
                        <DialogTitle>Entry Details</DialogTitle>
                        <DialogDescription>
                            {{ viewingEntry?.name }}
                        </DialogDescription>
                    </DialogHeader>
                    <div class="max-h-[60vh] space-y-4 overflow-y-auto">
                        <div v-if="viewingEntry?.summary">
                            <h4 class="mb-2 text-sm font-semibold">Summary</h4>
                            <p
                                class="text-sm leading-relaxed text-muted-foreground"
                            >
                                {{ viewingEntry.summary }}
                            </p>
                        </div>

                        <Separator
                            v-if="
                                viewingEntry?.summary &&
                                viewingEntry?.chapters?.length
                            "
                        />

                        <div v-if="viewingEntry?.chapters?.length">
                            <h4 class="mb-2 text-sm font-semibold">Chapters</h4>
                            <ul class="space-y-2">
                                <li
                                    v-for="(
                                        chapter, index
                                    ) in viewingEntry.chapters"
                                    :key="index"
                                    class="flex items-center gap-3"
                                >
                                    <Badge
                                        variant="secondary"
                                        class="shrink-0 font-mono text-xs"
                                    >
                                        <Clock class="mr-1 h-3 w-3" />
                                        {{ formatTimestamp(chapter.startTime) }}
                                    </Badge>
                                    <span class="text-sm">{{
                                        chapter.title
                                    }}</span>
                                </li>
                            </ul>
                        </div>

                        <Separator
                            v-if="
                                (viewingEntry?.summary ||
                                    viewingEntry?.chapters?.length) &&
                                parsedTranscription(viewingEntry)
                            "
                        />

                        <div v-if="parsedTranscription(viewingEntry)">
                            <h4 class="mb-2 text-sm font-semibold">
                                Transcription
                            </h4>
                            <div class="rounded-md border p-4">
                                <div
                                    v-for="(
                                        segment, index
                                    ) in parsedTranscription(viewingEntry)"
                                    :key="index"
                                    class="mb-2 last:mb-0"
                                >
                                    <span
                                        class="mr-2 text-xs font-medium text-muted-foreground"
                                    >
                                        [{{
                                            formatTimestamp(
                                                segment.start_seconds,
                                            )
                                        }}] {{ segment.speaker }}:
                                    </span>
                                    <span class="text-sm leading-relaxed">{{
                                        segment.text
                                    }}</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </DialogContent>
            </Dialog>

            <div class="rounded-md border bg-white dark:bg-zinc-950">
                <Table>
                    <TableHeader>
                        <TableRow>
                            <TableHead class="w-[45%]">Name</TableHead>
                            <TableHead class="w-[15%]">File</TableHead>
                            <TableHead class="w-[20%]">Details</TableHead>
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
                                        v-if="entry.audio_url"
                                        :href="getEntryFile.url(entry.id)"
                                        target="_blank"
                                        class="text-blue-600 hover:underline dark:text-blue-400"
                                    >
                                        View
                                    </a>
                                    <span
                                        v-else
                                        class="text-gray-400 dark:text-gray-600"
                                        >-</span
                                    >
                                </TableCell>
                                <TableCell class="align-top">
                                    <div class="flex items-center gap-3">
                                        <span
                                            v-if="
                                                getBatchStatus(entry) ===
                                                'pending'
                                            "
                                            class="flex items-center gap-1 text-sm text-muted-foreground"
                                        >
                                            <Loader2
                                                class="h-3 w-3 animate-spin"
                                            />
                                            Pending
                                        </span>
                                        <button
                                            v-else-if="
                                                getBatchStatus(entry) ===
                                                'failed'
                                            "
                                            class="text-sm text-red-500 hover:underline"
                                            @click="viewingFailure = entry"
                                        >
                                            Failed
                                        </button>
                                        <button
                                            v-else-if="hasDetails(entry)"
                                            class="text-blue-600 hover:underline dark:text-blue-400"
                                            @click="viewingEntry = entry"
                                        >
                                            View
                                        </button>
                                        <span
                                            v-else
                                            class="text-gray-400 dark:text-gray-600"
                                            >-</span
                                        >
                                        <button
                                            v-if="
                                                entry.audio_url &&
                                                getBatchStatus(entry) !==
                                                    'pending'
                                            "
                                            class="text-gray-500 hover:underline dark:text-gray-400"
                                            @click="
                                                regenerateTranscription(
                                                    entry.id,
                                                )
                                            "
                                        >
                                            Regenerate
                                        </button>
                                    </div>
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
                            <TableCell colspan="4" class="h-24 text-center">
                                No entries yet. Click "Add Entry" to create one.
                            </TableCell>
                        </TableRow>
                    </TableBody>
                </Table>
            </div>
        </div>
    </AppLayout>
</template>
