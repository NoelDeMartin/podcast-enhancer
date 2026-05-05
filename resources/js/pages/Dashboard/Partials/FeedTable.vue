<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import MoreHorizontal from '~icons/lucide/more-horizontal';
import Rss from '~icons/lucide/rss';
import { show } from '@/actions/App/Http/Controllers/FeedController';
import { Button } from '@/components/ui/button';
import {
    DropdownMenu,
    DropdownMenuContent,
    DropdownMenuItem,
    DropdownMenuTrigger,
} from '@/components/ui/dropdown-menu';
import {
    Table,
    TableBody,
    TableCell,
    TableHead,
    TableHeader,
    TableRow,
} from '@/components/ui/table';

defineProps<{
    feeds: any[];
}>();

const emit = defineEmits<{
    edit: [feed: any];
    delete: [feed: any];
}>();

const SYNC_FREQUENCIES: Record<number, string> = {
    0: 'Manual',
    3600: 'Hourly',
    21600: '6 hours',
    43200: '12 hours',
    86400: 'Daily',
    604800: 'Weekly',
};

const getSyncStatus = (feed: any) => {
    if (!feed.rss_url) {
        return {
            indicator: 'bg-gray-400',
            label: 'Manual',
        };
    }

    if (!feed.last_synced_at) {
        return {
            indicator: 'bg-gray-400',
            label: 'Never synced',
        };
    }

    if (feed.sync_frequency === 0) {
        return {
            indicator: 'bg-gray-400',
            label: 'Manual only',
        };
    }

    const lastSynced = new Date(feed.last_synced_at);
    const now = new Date();
    const threshold = new Date(lastSynced.getTime() + (feed.sync_frequency ?? 0) * 1000);

    if (now > threshold) {
        return {
            indicator: 'bg-yellow-400',
            label: 'Needs sync',
        };
    }

    return {
        indicator: 'bg-green-400',
        label: 'Up to date',
    };
};

const formatLastSynced = (date: string) => {
    return new Date(date).toLocaleString(undefined, {
        month: 'short',
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit',
    });
};
</script>

<template>
    <div class="border-3 bg-background shadow-neo-hard-hard">
        <Table>
            <TableHeader>
                <TableRow>
                    <TableHead class="w-[10%]">Image</TableHead>
                    <TableHead class="w-[30%]">Title</TableHead>
                    <TableHead>Entries</TableHead>
                    <TableHead>Sync Status</TableHead>
                    <TableHead class="text-right">Actions</TableHead>
                </TableRow>
            </TableHeader>
            <TableBody>
                <TableRow v-for="feed in feeds" :key="feed.id">
                    <TableCell>
                        <img
                            v-if="feed.absolute_image_url"
                            :src="feed.absolute_image_url"
                            alt=""
                            class="h-10 w-10 rounded-none object-cover"
                        />
                        <div
                            v-else
                            class="flex h-10 w-10 items-center justify-center rounded-none bg-gray-100 dark:bg-zinc-800"
                        >
                            <Rss class="h-5 w-5 text-gray-400" />
                        </div>
                    </TableCell>
                    <TableCell class="font-medium">
                        <Link :href="show.url(feed.slug)" class="hover:underline">
                            {{ feed.title }}
                        </Link>
                    </TableCell>
                    <TableCell>{{ feed.entries_count }}</TableCell>
                    <TableCell>
                        <div class="flex flex-col gap-1 text-xs">
                            <div class="flex items-center gap-2">
                                <span
                                    :class="['h-2 w-2 rounded-full', getSyncStatus(feed).indicator]"
                                />
                                <span class="text-sm font-medium">{{
                                    getSyncStatus(feed).label
                                }}</span>
                            </div>
                            <div class="text-muted-foreground">
                                Freq:
                                {{ SYNC_FREQUENCIES[feed.sync_frequency ?? 0] ?? 'Manual' }}
                            </div>
                            <div v-if="feed.last_synced_at" class="text-muted-foreground">
                                Last:
                                {{ formatLastSynced(feed.last_synced_at) }}
                            </div>
                        </div>
                    </TableCell>
                    <TableCell class="text-right">
                        <DropdownMenu>
                            <DropdownMenuTrigger as-child>
                                <Button variant="ghost" class="h-8 w-8 p-0">
                                    <span class="sr-only">Open menu</span>
                                    <MoreHorizontal class="h-4 w-4" />
                                </Button>
                            </DropdownMenuTrigger>
                            <DropdownMenuContent align="end">
                                <DropdownMenuItem @click="emit('edit', feed)">
                                    Edit
                                </DropdownMenuItem>
                                <DropdownMenuItem
                                    class="text-red-600"
                                    @click="emit('delete', feed)"
                                >
                                    Delete
                                </DropdownMenuItem>
                            </DropdownMenuContent>
                        </DropdownMenu>
                    </TableCell>
                </TableRow>
                <TableRow v-if="feeds.length === 0">
                    <TableCell colspan="5" class="h-24 text-center">
                        No feeds created yet.
                    </TableCell>
                </TableRow>
            </TableBody>
        </Table>
    </div>
</template>
