<script setup lang="ts">
import { useForm } from '@inertiajs/vue3';
import { Sparkles } from 'lucide-vue-next';
import { produce as produceEntry } from '@/actions/App/Http/Controllers/EntryController';
import { Button } from '@/components/ui/button';
import {
    DropdownMenu,
    DropdownMenuContent,
    DropdownMenuItem,
    DropdownMenuTrigger,
} from '@/components/ui/dropdown-menu';
import { getBatchStatus } from '@/lib/entries';

const props = withDefaults(
    defineProps<{
        feed: { slug: string };
        entry: any;
        type?: 'dropdown-items' | 'button';
    }>(),
    {
        type: 'button',
    },
);

const regenerateTranscription = () => {
    useForm({}).submit(produceEntry([props.feed.slug, props.entry.slug]));
};

const regenerateMetadata = () => {
    useForm({ reuse_transcript: true }).submit(
        produceEntry([props.feed.slug, props.entry.slug]),
    );
};

const isPending = () => getBatchStatus(props.entry) === 'pending';
const hasTranscription = () => !!props.entry.transcription_path;
</script>

<template>
    <template v-if="type === 'dropdown-items'">
        <DropdownMenuItem
            v-if="
                entry.audio_url &&
                !isPending() &&
                (!hasTranscription()
                    ? entry.can?.produce
                    : entry.can?.regenerate)
            "
            @click="regenerateTranscription"
        >
            {{
                hasTranscription()
                    ? 'Regenerate enhancements'
                    : 'Generate enhancements'
            }}
        </DropdownMenuItem>

        <DropdownMenuItem
            v-if="hasTranscription() && !isPending() && entry.can?.regenerate"
            @click="regenerateMetadata"
        >
            Regenerate (only chapters & summary)
        </DropdownMenuItem>
    </template>

    <template v-else-if="!isPending() && entry.audio_url">
        <DropdownMenu
            v-if="
                !hasTranscription() ? entry.can?.produce : entry.can?.regenerate
            "
        >
            <DropdownMenuTrigger as-child>
                <Button variant="outline">
                    <Sparkles class="mr-2 h-4 w-4" />
                    Enhance
                </Button>
            </DropdownMenuTrigger>
            <DropdownMenuContent align="end">
                <DropdownMenuItem
                    v-if="
                        !hasTranscription()
                            ? entry.can?.produce
                            : entry.can?.regenerate
                    "
                    @click="regenerateTranscription"
                >
                    {{
                        hasTranscription()
                            ? 'Regenerate everything'
                            : 'Generate enhancements'
                    }}
                </DropdownMenuItem>
                <DropdownMenuItem
                    v-if="hasTranscription() && entry.can?.regenerate"
                    @click="regenerateMetadata"
                >
                    Regenerate (only chapters & summary)
                </DropdownMenuItem>
            </DropdownMenuContent>
        </DropdownMenu>
    </template>
</template>
