<script setup lang="ts">
import { DropdownMenuItem } from '@/components/ui/dropdown-menu';
import { useEntryEnhancementActions } from '@/composables/useEntryEnhancementActions';

const props = defineProps<{
    feed: { slug: string };
    entry: any;
}>();

const actions = useEntryEnhancementActions(
    () => props.feed,
    () => props.entry,
);
</script>

<template>
    <DropdownMenuItem
        v-for="action in actions"
        :key="action.key"
        class="gap-1.5 font-bold uppercase"
        @click="action.run"
    >
        <i-carbon-ai-generate class="size-4" />
        {{ action.label }}
    </DropdownMenuItem>
</template>
