<script setup lang="ts">
import { Link } from '@inertiajs/vue3';

import PaginationLinkLabel from '@/components/PaginationLinkLabel.vue';
import { type PaginationLink, paginationLinkClasses, paginationLinkRel } from '@/lib/pagination';

defineProps<{
    label?: string;
    links: PaginationLink[];
}>();
</script>

<template>
    <nav
        v-if="links.length > 3"
        role="navigation"
        :aria-label="label || 'Pages'"
        class="flex flex-wrap items-center justify-center gap-1"
    >
        <template v-for="(link, key) in links" :key="key">
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
