<script setup lang="ts">
import { Link } from '@inertiajs/vue3';

defineProps<{
    label?: string;
    links: {
        url: string | null;
        label: string;
        active: boolean;
    }[];
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
            <div
                v-if="link.url === null"
                class="border-neo-dark border-3 bg-white px-4 py-3 text-sm leading-4 text-gray-400 opacity-50"
                v-html="link.label"
            />
            <Link
                v-else
                class="border-neo-dark hover:bg-primary focus-visible:ring-primary border-3 px-4 py-3 text-sm leading-4 transition-all hover:text-white focus-visible:ring-2 focus-visible:ring-offset-1 focus-visible:outline-none"
                :class="{
                    'bg-primary hover:bg-primary text-white': link.active,
                    'bg-white': !link.active,
                }"
                :href="link.url"
                :rel="
                    link.label.includes('&larr;')
                        ? 'prev'
                        : link.label.includes('&rarr;')
                          ? 'next'
                          : null
                "
                :aria-current="link.active ? 'page' : null"
                v-html="link.label"
                preserve-scroll
            />
        </template>
    </nav>
</template>
