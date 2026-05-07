<script setup lang="ts">
import { useForm } from '@inertiajs/vue3';
import { ref } from 'vue';
import { computed } from 'vue';
import Renew from '~icons/carbon/renew';

import {
    fetch as fetchRssAction,
    store as storeRssAction,
} from '@/actions/App/Http/Controllers/RssImportController';
import { Button } from '@/components/ui/button';
import { Checkbox } from '@/components/ui/checkbox';
import { DialogDescription, DialogFooter, DialogHeader, DialogTitle } from '@/components/ui/dialog';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Modal } from '@/components/ui/modal';
import {
    Table,
    TableBody,
    TableCell,
    TableHead,
    TableHeader,
    TableRow,
} from '@/components/ui/table';

const props = defineProps<{
    feed: any;
}>();

const emit = defineEmits<{
    close: [];
}>();

const rssUrl = ref('');
const episodes = ref<any[]>([]);
const selectedEpisodes = ref<number[]>([]);
const isFetchingRss = ref(false);
const rssError = ref('');

const title = computed(() =>
    episodes.value.length === 0 ? 'Import from RSS Podcast' : 'Select Episodes to Import',
);
const description = computed(() =>
    episodes.value.length === 0
        ? 'Enter the RSS podcast URL to fetch episodes.'
        : 'Select the episodes you want to add to this podcast.',
);

const fetchRss = async () => {
    isFetchingRss.value = true;
    rssError.value = '';

    try {
        const response = await fetch(fetchRssAction(props.feed).url, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                Accept: 'application/json',
                'X-CSRF-TOKEN':
                    (document.querySelector('meta[name="csrf-token"]') as HTMLMetaElement)
                        ?.content || '',
            },
            body: JSON.stringify({ url: rssUrl.value }),
        });

        if (!response.ok) {
            const data = await response.json();
            throw new Error(data.message || 'Failed to fetch RSS podcast.');
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
    const selected = episodes.value.filter((_, i) => selectedEpisodes.value.includes(i));
    useForm({ episodes: selected }).post(storeRssAction(props.feed).url, {
        onSuccess: () => {
            emit('close');
        },
    });
};

const toggleEpisode = (index: number) => {
    if (selectedEpisodes.value.includes(index)) {
        selectedEpisodes.value = selectedEpisodes.value.filter((i) => i !== index);
    } else {
        selectedEpisodes.value.push(index);
    }
};
</script>

<template>
    <Modal :title :description class="sm:max-w-[600px]">
        <div v-if="episodes.length === 0">
            <div class="grid gap-4 py-4">
                <div class="grid gap-2">
                    <Label for="rss-url">RSS URL</Label>
                    <Input
                        id="rss-url"
                        v-model="rssUrl"
                        placeholder="https://example.com/podcast.xml"
                        :disabled="isFetchingRss"
                    />
                    <p v-if="rssError" class="text-sm text-red-500">
                        {{ rssError }}
                    </p>
                </div>
            </div>
            <DialogFooter>
                <Button :disabled="isFetchingRss || !rssUrl" @click="fetchRss">
                    <Renew v-if="isFetchingRss" class="mr-2 size-4 animate-spin" />
                    Fetch Episodes
                </Button>
            </DialogFooter>
        </div>
        <div v-else>
            <div class="border-neo-dark my-4 max-h-[400px] overflow-y-auto rounded-none border-3">
                <Table>
                    <TableHeader>
                        <TableRow>
                            <TableHead class="w-[50px]"></TableHead>
                            <TableHead>Episode</TableHead>
                        </TableRow>
                    </TableHeader>
                    <TableBody>
                        <TableRow v-for="(episode, index) in episodes" :key="index">
                            <TableCell>
                                <Checkbox
                                    :model-value="selectedEpisodes.includes(index)"
                                    :disabled="!episode.audio_url"
                                    @update:modelValue="toggleEpisode(index)"
                                />
                            </TableCell>
                            <TableCell>
                                <div class="font-medium">
                                    {{ episode.name }}
                                </div>
                                <div v-if="!episode.audio_url" class="text-xs text-red-500">
                                    No audio URL found
                                </div>
                            </TableCell>
                        </TableRow>
                    </TableBody>
                </Table>
            </div>
            <DialogFooter class="flex justify-between sm:justify-between">
                <Button variant="ghost" @click="episodes = []">Back</Button>
                <Button :disabled="selectedEpisodes.length === 0" @click="importRss">
                    Import {{ selectedEpisodes.length }} Episodes
                </Button>
            </DialogFooter>
        </div>
    </Modal>
</template>
