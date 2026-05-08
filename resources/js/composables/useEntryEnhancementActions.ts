import { useForm } from '@inertiajs/vue3';
import { computed, type ComputedRef } from 'vue';

import { produce as produceEntry } from '@/actions/App/Http/Controllers/EntryController';
import { getBatchStatus } from '@/lib/entries';

export type EntryEnhancementActionKey = 'enhance' | 'metadata-only';

export type EntryEnhancementAction = {
    key: EntryEnhancementActionKey;
    label: string;
    run: () => void;
};

export function useEntryEnhancementActions(
    feed: () => { slug: string },
    entry: () => any,
): ComputedRef<EntryEnhancementAction[]> {
    return computed<EntryEnhancementAction[]>(() => {
        const feedValue = feed();
        const entryValue = entry();

        if (!entryValue) {
            return [];
        }

        if (!(entryValue.can?.produce || entryValue.can?.regenerate)) {
            return [];
        }

        if (getBatchStatus(entryValue) === 'pending') {
            return [];
        }

        const actions: EntryEnhancementAction[] = [];
        const hasTranscription = !!entryValue.transcription_path;

        const canRunPrimary = hasTranscription
            ? !!entryValue.can?.regenerate
            : !!entryValue.can?.produce;

        if (entryValue.audio_url && canRunPrimary) {
            actions.push({
                key: 'enhance',
                label: hasTranscription ? 'Regenerate enhancements' : 'Generate enhancements',
                run: () => {
                    useForm({}).submit(produceEntry([feedValue.slug, entryValue.slug]));
                },
            });
        }

        if (hasTranscription && !!entryValue.can?.regenerate) {
            actions.push({
                key: 'metadata-only',
                label: 'Regenerate (only chapters & summary)',
                run: () => {
                    useForm({ reuse_transcript: true }).submit(
                        produceEntry([feedValue.slug, entryValue.slug]),
                    );
                },
            });
        }

        return actions;
    });
}
