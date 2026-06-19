<script setup lang="ts">
import { computed } from 'vue';

import PaginationLinkLabel from '@/components/PaginationLinkLabel.vue';
import { type PaginationLink, paginationLinkClasses, collapsePaginationLinks } from '@/lib/pagination';

const props = defineProps<{
    label?: string;
    links: PaginationLink[];
    onPageChange: (url: string) => void;
}>();

const collapsedLinks = computed(() => collapsePaginationLinks(props.links));

function handlePageChange(url: string | null) {
    if (url) {
        props.onPageChange(url);
    }
}
</script>

<template>
    <nav
        v-if="collapsedLinks.length > 3"
        role="navigation"
        :aria-label="label || 'Pages'"
        class="flex flex-wrap items-center justify-center gap-1"
    >
        <template v-for="(link, key) in collapsedLinks" :key="key">
            <component
                :is="link.url === null ? 'div' : 'button'"
                :type="link.url === null ? undefined : 'button'"
                :class="paginationLinkClasses(link)"
                :aria-current="link.active ? 'page' : undefined"
                @click="handlePageChange(link.url)"
            >
                <PaginationLinkLabel :label="link.label" />
            </component>
        </template>
    </nav>
</template>
