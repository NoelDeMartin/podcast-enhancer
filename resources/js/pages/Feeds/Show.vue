<script setup lang="ts">
import { Head, Link, usePoll } from '@inertiajs/vue3';
import { useForm } from '@inertiajs/vue3';
import DOMPurify from 'dompurify';
import linkifyHtml from 'linkify-html';
import {
    Clock,
    Loader2,
    MoreHorizontal,
    Plus,
    Rss,
    RefreshCw,
} from 'lucide-vue-next';
import { computed, ref, watch } from 'vue';
import {
    store as storeEntry,
    destroy as destroyEntry,
    update as updateEntryAction,
    file as getEntryFile,
    produce as produceEntry,
    show as showEntryAction,
} from '@/actions/App/Http/Controllers/EntryController';
import FeedRssController from '@/actions/App/Http/Controllers/FeedRssController';
import { sync as syncFeedAction } from '@/actions/App/Http/Controllers/FeedSyncController';
import {
    fetch as fetchRssAction,
    store as storeRssAction,
} from '@/actions/App/Http/Controllers/RssImportController';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Checkbox } from '@/components/ui/checkbox';
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

function formatDatetimeLocalForInput(date: Date): string {
    const pad = (n: number) => String(n).padStart(2, '0');

    return `${date.getFullYear()}-${pad(date.getMonth() + 1)}-${pad(date.getDate())}T${pad(date.getHours())}:${pad(date.getMinutes())}`;
}

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
    image_url: '',
    image_file: null as File | null,
    published_at: formatDatetimeLocalForInput(new Date()),
});

const entrySource = ref<'url' | 'file'>('url');
const entryImageSource = ref<'url' | 'file'>('url');

const showEntryForm = ref(false);

const submitEntry = () => {
    entryForm.submit(storeEntry(), {
        onSuccess: () => {
            entryForm.reset(
                'name',
                'audio_url',
                'file',
                'image_url',
                'image_file',
            );
            entryForm.published_at = formatDatetimeLocalForInput(new Date());
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
    useForm({}).submit(produceEntry(id));
};

const regenerateMetadata = (id: number) => {
    useForm({ reuse_transcript: true }).submit(produceEntry(id));
};

const showRssModal = ref(false);
const rssUrl = ref('');
const episodes = ref<any[]>([]);
const selectedEpisodes = ref<number[]>([]);
const isFetchingRss = ref(false);
const rssError = ref('');

const fetchRss = async () => {
    isFetchingRss.value = true;
    rssError.value = '';

    try {
        const response = await fetch(fetchRssAction(props.feed.id).url, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                Accept: 'application/json',
                'X-CSRF-TOKEN':
                    (
                        document.querySelector(
                            'meta[name="csrf-token"]',
                        ) as HTMLMetaElement
                    )?.content || '',
            },
            body: JSON.stringify({ url: rssUrl.value }),
        });

        if (!response.ok) {
            const data = await response.json();

            throw new Error(data.message || 'Failed to fetch RSS feed.');
        }

        const data = await response.json();
        episodes.value = data.episodes;
        selectedEpisodes.value = [];
    } catch (e: any) {
        rssError.value = e.message;
    } finally {
        isFetchingRss.value = false;
    }
};

const importRss = () => {
    const selected = episodes.value.filter((_, i) =>
        selectedEpisodes.value.includes(i),
    );
    useForm({ episodes: selected }).submit(storeRssAction(props.feed.id), {
        onSuccess: () => {
            showRssModal.value = false;
            rssUrl.value = '';
            episodes.value = [];
            selectedEpisodes.value = [];
        },
    });
};

const toggleEpisode = (index: number) => {
    if (selectedEpisodes.value.includes(index)) {
        selectedEpisodes.value = selectedEpisodes.value.filter(
            (i) => i !== index,
        );
    } else {
        selectedEpisodes.value.push(index);
    }
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

function formatPublishedAt(entry: any): string {
    const value = entry?.published_at;

    if (!value) {
        return '-';
    }

    const date = new Date(value);

    if (Number.isNaN(date.getTime())) {
        return '-';
    }

    return new Intl.DateTimeFormat(undefined, {
        year: 'numeric',
        month: 'short',
        day: '2-digit',
    }).format(date);
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

function formatSummary(summary: string | null | undefined): string {
    if (!summary) {
        return '';
    }

    const linkedSummary = linkifyHtml(summary, {
        defaultProtocol: 'https',
        target: '_blank',
        rel: 'noopener noreferrer',
    });

    return DOMPurify.sanitize(linkedSummary, { ADD_ATTR: ['target'] });
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
    image_url: '',
    image_file: null as File | null,
    delete_image_file: false,
});

const editEntrySource = ref<'url' | 'file'>('url');
const editEntryImageSource = ref<'url' | 'file'>('url');

const isExternal = (url: string | null) => {
    if (!url) {
        return false;
    }

    return url.startsWith('http://') || url.startsWith('https://');
};

const isSyncing = ref(false);
const syncFeed = () => {
    isSyncing.value = true;
    useForm({}).post(syncFeedAction.url(props.feed.id), {
        onFinish: () => {
            isSyncing.value = false;
        },
    });
};

const startEditEntry = (entry: any) => {
    editingEntry.value = entry;
    editEntryForm.name = entry.name;
    const external = isExternal(entry.audio_url);
    editEntrySource.value = external ? 'url' : 'file';
    editEntryForm.audio_url = external ? entry.audio_url : '';
    editEntryForm.file = null;
    editEntryForm.delete_file = false;

    const externalImage = isExternal(entry.image_url);
    editEntryImageSource.value = externalImage ? 'url' : 'file';
    editEntryForm.image_url = externalImage ? entry.image_url : '';
    editEntryForm.image_file = null;
    editEntryForm.delete_image_file = false;

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

                <div class="flex items-center gap-2">
                    <Button
                        v-if="feed.rss_url"
                        @click="syncFeed"
                        :disabled="isSyncing"
                    >
                        <Loader2
                            v-if="isSyncing"
                            class="mr-2 h-4 w-4 animate-spin"
                        />
                        <RefreshCw v-else class="mr-2 h-4 w-4" />
                        Synchronize
                    </Button>
                    <template v-else>
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
                                            <Label for="published_at"
                                                >Published at</Label
                                            >
                                            <Input
                                                id="published_at"
                                                v-model="entryForm.published_at"
                                                type="datetime-local"
                                                required
                                            />
                                            <div
                                                v-if="
                                                    entryForm.errors
                                                        .published_at
                                                "
                                                class="text-sm text-red-500"
                                            >
                                                {{
                                                    entryForm.errors
                                                        .published_at
                                                }}
                                            </div>
                                        </div>
                                        <div class="grid gap-2">
                                            <Label for="source"
                                                >Source Type</Label
                                            >
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
                                            <Label for="audio_url"
                                                >Audio URL</Label
                                            >
                                            <Input
                                                id="audio_url"
                                                v-model="entryForm.audio_url"
                                                placeholder="https://example.com/audio.mp3"
                                            />
                                            <div
                                                v-if="
                                                    entryForm.errors.audio_url
                                                "
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
                                        <div class="grid gap-2">
                                            <Label for="entryImageSource"
                                                >Image Source</Label
                                            >
                                            <Select v-model="entryImageSource">
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
                                            v-if="entryImageSource === 'url'"
                                            class="grid gap-2"
                                        >
                                            <Label for="image_url"
                                                >Image URL</Label
                                            >
                                            <Input
                                                id="image_url"
                                                v-model="entryForm.image_url"
                                                placeholder="https://example.com/image.jpg"
                                            />
                                            <div
                                                v-if="
                                                    entryForm.errors.image_url
                                                "
                                                class="text-sm text-red-500"
                                            >
                                                {{ entryForm.errors.image_url }}
                                            </div>
                                        </div>
                                        <div
                                            v-if="entryImageSource === 'file'"
                                            class="grid gap-2"
                                        >
                                            <Label for="image_file"
                                                >Image File</Label
                                            >
                                            <Input
                                                id="image_file"
                                                type="file"
                                                @input="
                                                    entryForm.image_file =
                                                        $event.target.files[0]
                                                "
                                                accept="image/*"
                                            />
                                            <div
                                                v-if="
                                                    entryForm.errors.image_file
                                                "
                                                class="text-sm text-red-500"
                                            >
                                                {{
                                                    entryForm.errors.image_file
                                                }}
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

                        <Dialog v-model:open="showRssModal">
                            <DialogTrigger as-child>
                                <Button variant="outline">
                                    <Rss class="mr-2 h-4 w-4" />
                                    Add from RSS
                                </Button>
                            </DialogTrigger>
                            <DialogContent class="sm:max-w-[600px]">
                                <div v-if="episodes.length === 0">
                                    <DialogHeader>
                                        <DialogTitle
                                            >Import from RSS Feed</DialogTitle
                                        >
                                        <DialogDescription>
                                            Enter the RSS feed URL to fetch
                                            episodes.
                                        </DialogDescription>
                                    </DialogHeader>
                                    <div class="grid gap-4 py-4">
                                        <div class="grid gap-2">
                                            <Label for="rss-url">RSS URL</Label>
                                            <Input
                                                id="rss-url"
                                                v-model="rssUrl"
                                                placeholder="https://example.com/feed.xml"
                                                :disabled="isFetchingRss"
                                            />
                                            <p
                                                v-if="rssError"
                                                class="text-sm text-red-500"
                                            >
                                                {{ rssError }}
                                            </p>
                                        </div>
                                    </div>
                                    <DialogFooter>
                                        <Button
                                            :disabled="isFetchingRss || !rssUrl"
                                            @click="fetchRss"
                                        >
                                            <Loader2
                                                v-if="isFetchingRss"
                                                class="mr-2 h-4 w-4 animate-spin"
                                            />
                                            Fetch Episodes
                                        </Button>
                                    </DialogFooter>
                                </div>
                                <div v-else>
                                    <DialogHeader>
                                        <DialogTitle
                                            >Select Episodes to
                                            Import</DialogTitle
                                        >
                                        <DialogDescription>
                                            Select the episodes you want to add
                                            to this feed.
                                        </DialogDescription>
                                    </DialogHeader>
                                    <div
                                        class="my-4 max-h-[400px] overflow-y-auto rounded-md border"
                                    >
                                        <Table>
                                            <TableHeader>
                                                <TableRow>
                                                    <TableHead
                                                        class="w-[50px]"
                                                    ></TableHead>
                                                    <TableHead
                                                        >Episode</TableHead
                                                    >
                                                </TableRow>
                                            </TableHeader>
                                            <TableBody>
                                                <TableRow
                                                    v-for="(
                                                        episode, index
                                                    ) in episodes"
                                                    :key="index"
                                                >
                                                    <TableCell>
                                                        <Checkbox
                                                            :model-value="
                                                                selectedEpisodes.includes(
                                                                    index,
                                                                )
                                                            "
                                                            :disabled="
                                                                !episode.audio_url
                                                            "
                                                            @update:modelValue="
                                                                toggleEpisode(
                                                                    index,
                                                                )
                                                            "
                                                        />
                                                    </TableCell>
                                                    <TableCell>
                                                        <div
                                                            class="font-medium"
                                                        >
                                                            {{ episode.name }}
                                                        </div>
                                                        <div
                                                            v-if="
                                                                !episode.audio_url
                                                            "
                                                            class="text-xs text-red-500"
                                                        >
                                                            No audio URL found
                                                        </div>
                                                    </TableCell>
                                                </TableRow>
                                            </TableBody>
                                        </Table>
                                    </div>
                                    <DialogFooter
                                        class="flex justify-between sm:justify-between"
                                    >
                                        <Button
                                            variant="ghost"
                                            @click="episodes = []"
                                            >Back</Button
                                        >
                                        <Button
                                            :disabled="
                                                selectedEpisodes.length === 0
                                            "
                                            @click="importRss"
                                        >
                                            Import
                                            {{ selectedEpisodes.length }}
                                            Episodes
                                        </Button>
                                    </DialogFooter>
                                </div>
                            </DialogContent>
                        </Dialog>
                    </template>
                </div>
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
                            <div class="grid gap-2">
                                <Label for="editEntryImageSource"
                                    >Image Source</Label
                                >
                                <Select v-model="editEntryImageSource">
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
                                v-if="editEntryImageSource === 'url'"
                                class="grid gap-2"
                            >
                                <Label for="edit-image_url">Image URL</Label>
                                <Input
                                    id="edit-image_url"
                                    v-model="editEntryForm.image_url"
                                    placeholder="https://example.com/image.jpg"
                                />
                                <div
                                    v-if="editEntryForm.errors.image_url"
                                    class="text-sm text-red-500"
                                >
                                    {{ editEntryForm.errors.image_url }}
                                </div>
                            </div>
                            <div
                                v-if="editEntryImageSource === 'file'"
                                class="grid gap-2"
                            >
                                <Label for="edit-image_file">Image File</Label>
                                <div
                                    v-if="
                                        !isExternal(editingEntry?.image_url) &&
                                        editingEntry?.image_url &&
                                        !editEntryForm.delete_image_file &&
                                        !editEntryForm.image_file
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
                                            editEntryForm.delete_image_file = true
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
                                            editEntryForm.image_file =
                                                $event.target.files[0]
                                        "
                                        accept="image/*"
                                    />
                                    <div
                                        v-if="editEntryForm.errors.image_file"
                                        class="text-sm text-red-500"
                                    >
                                        {{ editEntryForm.errors.image_file }}
                                    </div>
                                    <Button
                                        v-if="
                                            editEntryForm.delete_image_file &&
                                            !isExternal(
                                                editingEntry?.image_url,
                                            ) &&
                                            editingEntry?.image_url
                                        "
                                        type="button"
                                        variant="link"
                                        size="sm"
                                        class="mt-1 px-0 text-gray-500"
                                        @click="
                                            editEntryForm.delete_image_file = false;
                                            editEntryForm.image_file = null;
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
                            <div
                                class="text-sm leading-relaxed text-muted-foreground [&_a]:text-blue-600 dark:[&_a]:text-blue-400 [&_a:hover]:underline [&_ol]:list-decimal [&_ol]:pl-5 [&_p]:mb-4 [&_ul]:list-disc [&_ul]:pl-5"
                                v-html="formatSummary(viewingEntry.summary)"
                            ></div>
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
                            <TableHead class="w-[10%]">Image</TableHead>
                            <TableHead class="w-[28%]">Name</TableHead>
                            <TableHead class="w-[12%]">Published</TableHead>
                            <TableHead class="w-[15%]">File</TableHead>
                            <TableHead class="w-[20%]">Details</TableHead>
                            <TableHead class="text-right">Actions</TableHead>
                        </TableRow>
                    </TableHeader>
                    <TableBody>
                        <template v-for="entry in feed.entries" :key="entry.id">
                            <TableRow>
                                <TableCell>
                                    <img
                                        v-if="entry.image_url || feed.image_url"
                                        :src="
                                            isExternal(
                                                entry.image_url ||
                                                    feed.image_url,
                                            )
                                                ? entry.image_url ||
                                                  feed.image_url
                                                : `/storage/${entry.image_url || feed.image_url}`
                                        "
                                        alt="Entry image"
                                        class="h-10 w-10 rounded object-cover"
                                    />
                                    <div
                                        v-else
                                        class="flex h-10 w-10 items-center justify-center rounded bg-gray-100 dark:bg-zinc-800"
                                    >
                                        <Rss class="h-5 w-5 text-gray-400" />
                                    </div>
                                </TableCell>
                                <TableCell class="align-top font-medium">
                                    <Link
                                        :href="
                                            showEntryAction.url([
                                                feed.id,
                                                entry.id,
                                            ])
                                        "
                                        class="hover:underline"
                                    >
                                        {{ entry.name }}
                                    </Link>
                                </TableCell>
                                <TableCell
                                    class="align-top text-sm text-muted-foreground"
                                >
                                    {{ formatPublishedAt(entry) }}
                                </TableCell>
                                <TableCell class="align-top">
                                    <a
                                        v-if="entry.audio_url"
                                        :href="
                                            entry.audio_url.startsWith('http')
                                                ? entry.audio_url
                                                : getEntryFile.url(entry.id)
                                        "
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
                                        <Link
                                            class="text-blue-600 hover:underline dark:text-blue-400"
                                            :href="
                                                showEntryAction.url([
                                                    feed.id,
                                                    entry.id,
                                                ])
                                            "
                                        >
                                            View
                                        </Link>
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
                                            <template v-if="!feed.rss_url">
                                                <DropdownMenuItem
                                                    @click="
                                                        startEditEntry(entry)
                                                    "
                                                >
                                                    Edit
                                                </DropdownMenuItem>
                                            </template>
                                            <DropdownMenuItem
                                                v-if="
                                                    entry.audio_url &&
                                                    getBatchStatus(entry) !==
                                                        'pending'
                                                "
                                                @click="
                                                    regenerateTranscription(
                                                        entry.id,
                                                    )
                                                "
                                            >
                                                Regenerate
                                            </DropdownMenuItem>
                                            <DropdownMenuItem
                                                v-if="
                                                    entry.transcription_path &&
                                                    getBatchStatus(entry) !==
                                                        'pending'
                                                "
                                                @click="
                                                    regenerateMetadata(entry.id)
                                                "
                                            >
                                                Regenerate (only chapters &
                                                summary)
                                            </DropdownMenuItem>
                                            <template v-if="!feed.rss_url">
                                                <DropdownMenuItem
                                                    class="text-red-600"
                                                    @click="
                                                        deleteEntry(entry.id)
                                                    "
                                                >
                                                    Delete
                                                </DropdownMenuItem>
                                            </template>
                                        </DropdownMenuContent>
                                    </DropdownMenu>
                                </TableCell>
                            </TableRow>
                        </template>
                        <TableRow v-if="feed.entries.length === 0">
                            <TableCell colspan="6" class="h-24 text-center">
                                No entries yet. Click "Add Entry" to create one.
                            </TableCell>
                        </TableRow>
                    </TableBody>
                </Table>
            </div>
        </div>
    </AppLayout>
</template>
