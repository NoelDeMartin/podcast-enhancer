<script setup lang="ts">
import { Link } from '@inertiajs/vue3';

defineProps<{
    links: {
        url: string | null;
        label: string;
        active: boolean;
    }[];
}>();
</script>

<template>
    <div v-if="links.length > 3">
        <div class="flex flex-wrap items-center justify-center gap-1">
            <template v-for="(link, key) in links" :key="key">
                <div
                    v-if="link.url === null"
                    class="border-neo-dark border-3 bg-white px-4 py-3 text-sm leading-4 text-gray-400 opacity-50"
                    v-html="link.label"
                />
                <Link
                    v-else
                    class="border-neo-dark hover:bg-neo-yellow focus:bg-neo-yellow border-3 px-4 py-3 text-sm leading-4 transition-all focus:outline-none"
                    :class="{
                        'bg-primary hover:bg-primary focus:bg-primary text-white': link.active,
                        'bg-white': !link.active,
                    }"
                    :href="link.url"
                    v-html="link.label"
                    preserve-scroll
                />
            </template>
        </div>
    </div>
</template>
