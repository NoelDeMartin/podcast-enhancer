<script setup lang="ts">
import { Link } from '@inertiajs/vue3';

import { show as showFeedAction } from '@/actions/App/Http/Controllers/FeedController';
import EntryEnhancementActions from '@/components/EntryEnhancementActions.vue';
import EntryEnhancementStatus from '@/components/EntryEnhancementStatus.vue';
import { Button } from '@/components/ui/button';
import {
    DropdownMenu,
    DropdownMenuContent,
    DropdownMenuTrigger,
} from '@/components/ui/dropdown-menu';
import { useEntryEnhancementActions } from '@/composables/useEntryEnhancementActions';
import { formatTimestamp } from '@/lib/entries';
import { formatDate } from '@/lib/utils';

const props = defineProps<{
    entry: any;
}>();

const enhancementActions = useEntryEnhancementActions(
    () => props.entry.feed,
    () => props.entry,
);
</script>

<template>
    <div class="flex flex-col gap-2">
        <div
            class="xs:grid-cols-[auto_1fr_auto] xs:grid-rows-[auto_auto] xs:[grid-template-areas:'img_title_actions'_'meta_meta_meta'] grid grid-cols-[1fr_auto] grid-rows-[auto_auto_auto] gap-x-4 gap-y-3 [grid-template-areas:'img_actions'_'title_title'_'meta_meta'] sm:grid-cols-[auto_1fr_auto] sm:grid-rows-[auto_auto] sm:items-start sm:gap-x-6 sm:gap-y-3 sm:[grid-template-areas:'img_title_actions'_'img_meta_meta']"
        >
            <div class="relative shrink-0 self-start [grid-area:img]">
                <img
                    v-if="entry.absolute_image_url || entry.feed.absolute_image_url"
                    :src="entry.absolute_image_url || entry.feed.absolute_image_url"
                    alt=""
                    class="border-neo-dark size-20 border-3 object-cover sm:size-24"
                />
            </div>

            <div class="min-w-0 [grid-area:title]">
                <div class="mb-1">
                    <Link
                        :href="showFeedAction.url(entry.feed.slug)"
                        class="text-neo-pink text-sm font-bold tracking-widest uppercase hover:underline"
                    >
                        {{ entry.feed.title }}
                    </Link>
                </div>
                <h2 class="text-2xl font-bold tracking-tight">
                    {{ entry.name }}
                </h2>
            </div>

            <div class="[grid-area:actions]">
                <Button
                    v-if="enhancementActions.length === 1"
                    variant="outline"
                    class="font-bold uppercase"
                    @click="enhancementActions[0].run"
                >
                    <i-carbon-ai-generate class="mr-2 size-4" />
                    {{ enhancementActions[0].label }}
                </Button>
                <DropdownMenu v-else-if="enhancementActions.length > 1">
                    <DropdownMenuTrigger as-child>
                        <Button variant="ghost" class="size-9">
                            <span class="sr-only">
                                Open enhancement menu for {{ entry.name }}
                            </span>
                            <i-carbon-overflow-menu-vertical class="size-6" />
                        </Button>
                    </DropdownMenuTrigger>
                    <DropdownMenuContent align="end">
                        <EntryEnhancementActions :feed="entry.feed" :entry="entry" />
                    </DropdownMenuContent>
                </DropdownMenu>
            </div>

            <div
                class="text-neo-dark/70 xs:flex-row xs:items-center xs:gap-x-4 xs:gap-y-1 flex flex-col flex-wrap items-start gap-x-0 gap-y-2 text-sm font-bold [grid-area:meta]"
            >
                <span class="inline-flex shrink-0 items-center gap-1.5">
                    <i-carbon-calendar class="size-4 shrink-0" />
                    {{ formatDate(entry.published_at) }}
                </span>
                <template v-if="entry.duration">
                    <span class="text-neo-dark/30 shrink-0 max-sm:hidden">•</span>
                    <span class="inline-flex shrink-0 items-center gap-1.5">
                        <i-carbon-time class="size-4 shrink-0" />
                        {{ formatTimestamp(entry.duration) }}
                    </span>
                </template>
                <EntryEnhancementStatus :entry="entry" show-bullet class="min-w-0 shrink-0" />
            </div>
        </div>
    </div>
</template>
