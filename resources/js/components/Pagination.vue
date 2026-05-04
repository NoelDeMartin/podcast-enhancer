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
                    class="border-3 border-neo-dark bg-white px-4 py-3 text-sm leading-4 text-gray-400 opacity-50"
                    v-html="link.label"
                />
                <Link
                    v-else
                    class="border-3 border-neo-dark px-4 py-3 text-sm leading-4 transition-all hover:bg-neo-yellow focus:bg-neo-yellow focus:outline-none"
                    :class="{
                        'bg-neo-pink text-white hover:bg-neo-pink focus:bg-neo-pink': link.active,
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
