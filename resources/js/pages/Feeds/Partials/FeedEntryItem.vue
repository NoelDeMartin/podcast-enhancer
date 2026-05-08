<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import { computed } from 'vue';
import Calendar from '~icons/carbon/calendar';
import Edit from '~icons/carbon/edit';
import OverflowMenuHorizontal from '~icons/carbon/overflow-menu-horizontal';
import Rss from '~icons/carbon/rss';
import Time from '~icons/carbon/time';
import TrashCan from '~icons/carbon/trash-can';

import { show as showEntryAction } from '@/actions/App/Http/Controllers/EntryController';
import EntryEnhancementActions from '@/components/EntryEnhancementActions.vue';
import EntryEnhancementStatus from '@/components/EntryEnhancementStatus.vue';
import { Button } from '@/components/ui/button';
import {
    DropdownMenu,
    DropdownMenuContent,
    DropdownMenuItem,
    DropdownMenuTrigger,
} from '@/components/ui/dropdown-menu';
import { formatDate, formatTimestamp, getBatchStatus } from '@/lib/entries';

const props = defineProps<{
    feed: any;
    entry: any;
    can: {
        update: boolean;
        delete: boolean;
    };
}>();

const emit = defineEmits<{
    edit: [entry: any];
    delete: [slug: string];
}>();

const entryImageRotationClasses = ['rotate-4', 'rotate-2', '-rotate-2', '-rotate-4'] as const;

const rotationClass = computed<(typeof entryImageRotationClasses)[number]>(() => {
    const seed = String(props.entry.id);
    let hash = 0;

    for (let i = 0; i < seed.length; i++) {
        hash = (hash * 31 + seed.charCodeAt(i)) | 0;
    }

    const index = Math.abs(hash) % entryImageRotationClasses.length;

    return entryImageRotationClasses[index];
});

const description = computed<string | null>(() => {
    const raw: string | null | undefined = props.entry.summary ?? props.entry.original_summary;

    if (!raw) {
        return null;
    }

    if (typeof document === 'undefined') {
        return (
            raw
                .replace(/<[^>]*>/g, ' ')
                .replace(/\s+/g, ' ')
                .trim() || null
        );
    }

    const container = document.createElement('div');
    container.innerHTML = raw;

    return container.textContent?.replace(/\s+/g, ' ').trim() || null;
});

/**
 * Matches EntryEnhancementActions (type="dropdown-items"): menu rows only render when
 * audio exists, batch is not pending, and policy allows produce/regenerate as applicable.
 */
const hasEnhancementDropdownItems = computed(() => {
    const entry = props.entry;

    if (!(entry.can?.produce || entry.can?.regenerate)) {
        return false;
    }

    const isPending = getBatchStatus(entry) === 'pending';
    const hasTranscription = !!entry.transcription_path;

    const primaryEnhancementItemVisible =
        !!entry.audio_url &&
        !isPending &&
        (hasTranscription ? !!entry.can?.regenerate : !!entry.can?.produce);

    const metadataOnlyItemVisible = hasTranscription && !isPending && !!entry.can?.regenerate;

    return primaryEnhancementItemVisible || metadataOnlyItemVisible;
});

/** Same conditions as FeedGrid episode actions: manual feeds get edit/delete when allowed. */
const hasManualFeedMenuItems = computed(
    () => !props.feed.rss_url && (props.can.update || props.can.delete),
);

const showEntryOverflowMenu = computed(
    () => hasManualFeedMenuItems.value || hasEnhancementDropdownItems.value,
);
</script>

<template>
    <li
        class="group border-neo-dark bg-neo-bg hover:shadow-neo-hard relative border-3 p-4 transition-all duration-300 sm:p-6"
    >
        <div
            class="xs:grid-cols-[auto_1fr_auto] xs:grid-rows-[auto_auto_auto] xs:[grid-template-areas:'img_title_actions'_'meta_meta_meta'_'desc_desc_desc'] grid grid-cols-[1fr_auto] grid-rows-[auto_auto_auto_auto] gap-x-4 gap-y-3 [grid-template-areas:'img_actions'_'title_title'_'meta_meta'_'desc_desc'] sm:grid-cols-[auto_1fr_auto] sm:grid-rows-[auto_1fr_auto] sm:items-start sm:gap-x-6 sm:gap-y-3 sm:[grid-template-areas:'img_title_actions'_'img_meta_meta'_'desc_desc_desc']"
        >
            <Link
                :href="showEntryAction.url([props.feed.slug, props.entry.slug])"
                class="relative shrink-0 self-start transition-transform duration-200 [grid-area:img] hover:scale-105 active:scale-95"
                tabindex="-1"
            >
                <img
                    v-if="entry.absolute_image_url || feed.absolute_image_url"
                    :src="entry.absolute_image_url || feed.absolute_image_url"
                    alt=""
                    :class="[
                        'border-neo-dark size-20 border-3 object-cover transition-transform duration-300 group-hover:rotate-0 sm:size-24',
                        rotationClass,
                    ]"
                />
                <div
                    v-else
                    :class="[
                        rotationClass,
                        'border-neo-dark bg-neo-yellow flex size-20 items-center justify-center border-3 transition-transform duration-300 group-hover:rotate-0 sm:size-24',
                    ]"
                >
                    <Rss class="text-neo-dark size-10" />
                </div>
            </Link>

            <div class="min-w-0 [grid-area:title]">
                <div class="flex items-start gap-2">
                    <Link
                        :href="showEntryAction.url([props.feed.slug, props.entry.slug])"
                        class="min-w-0 flex-1 text-lg leading-snug font-black tracking-tight wrap-break-word uppercase hover:underline sm:text-xl sm:leading-tight md:text-2xl"
                    >
                        {{ entry.name }}
                    </Link>
                </div>
            </div>

            <div class="size-9 [grid-area:actions]">
                <DropdownMenu v-if="showEntryOverflowMenu">
                    <DropdownMenuTrigger as-child>
                        <Button variant="ghost" class="size-9">
                            <span class="sr-only">Open menu for {{ entry.name }}</span>
                            <OverflowMenuHorizontal class="size-6" />
                        </Button>
                    </DropdownMenuTrigger>
                    <DropdownMenuContent align="end">
                        <template v-if="!feed.rss_url && can.update">
                            <DropdownMenuItem
                                class="gap-1.5 font-bold uppercase"
                                @click="emit('edit', entry)"
                            >
                                <Edit class="size-4" />
                                Edit
                            </DropdownMenuItem>
                        </template>

                        <EntryEnhancementActions
                            v-if="hasEnhancementDropdownItems"
                            :feed="feed"
                            :entry="entry"
                            type="dropdown-items"
                        />

                        <template v-if="!feed.rss_url && can.delete">
                            <DropdownMenuItem
                                class="gap-1.5 font-bold text-red-600 uppercase"
                                @click="emit('delete', entry.slug)"
                            >
                                <TrashCan class="size-4" />
                                Delete
                            </DropdownMenuItem>
                        </template>
                    </DropdownMenuContent>
                </DropdownMenu>
            </div>

            <div
                class="text-neo-dark/70 xs:flex-row xs:items-center xs:gap-x-4 xs:gap-y-1 flex flex-col flex-wrap items-start gap-x-0 gap-y-2 text-sm font-bold [grid-area:meta]"
            >
                <span class="inline-flex shrink-0 items-center gap-1.5">
                    <Calendar class="size-4 shrink-0" />
                    {{ formatDate(entry.published_at) }}
                </span>
                <template v-if="entry.duration">
                    <span class="text-neo-dark/30 shrink-0 max-sm:hidden">•</span>
                    <span class="inline-flex shrink-0 items-center gap-1.5">
                        <Time class="size-4 shrink-0" />
                        {{ formatTimestamp(entry.duration) }}
                    </span>
                </template>
                <EntryEnhancementStatus :entry="entry" show-bullet class="min-w-0 shrink-0" />
            </div>

            <p
                v-if="description"
                class="text-neo-dark/80 line-clamp-3 text-sm leading-relaxed font-medium [grid-area:desc]"
            >
                {{ description }}
            </p>
        </div>
    </li>
</template>
