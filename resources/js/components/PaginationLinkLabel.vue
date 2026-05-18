<script setup lang="ts">
import { arrowHtml, navLinkType, navText } from '@/lib/pagination';

defineProps<{
    label: string;
}>();
</script>

<template>
    <template v-if="navLinkType(label)">
        <span class="sm:hidden" aria-hidden="true" v-html="arrowHtml(label, navLinkType(label)!)" />
        <span class="sr-only sm:hidden">{{ navText(label) }}</span>
        <span class="hidden sm:inline" v-html="label" />
    </template>
    <span v-else v-html="label" />
</template>
