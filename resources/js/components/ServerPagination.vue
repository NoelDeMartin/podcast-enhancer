<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import { computed } from 'vue';

import PaginationLinkLabel from '@/components/PaginationLinkLabel.vue';
import {
    type PaginationLink,
    paginationLinkClasses,
    paginationLinkRel,
    collapsePaginationLinks,
} from '@/lib/pagination';

const props = defineProps<{
    label?: string;
    links: PaginationLink[];
}>();

const collapsedLinks = computed(() => collapsePaginationLinks(props.links));
</script>

<template>
    <nav
        v-if="collapsedLinks.length > 3"
        role="navigation"
        :aria-label="label || 'Pages'"
        class="flex flex-wrap items-center justify-center gap-1"
    >
        <template v-for="(link, key) in collapsedLinks" :key="key">
            <div v-if="link.url === null" :class="paginationLinkClasses(link)">
                <PaginationLinkLabel :label="link.label" />
            </div>
            <Link
                v-else
                :class="paginationLinkClasses(link)"
                :href="link.url"
                :rel="paginationLinkRel(link.label)"
                :aria-current="link.active ? 'page' : null"
                preserve-scroll
            >
                <PaginationLinkLabel :label="link.label" />
            </Link>
        </template>
    </nav>
</template>
