<script setup lang="ts">
const props = defineProps<{
    label?: string;
    links: {
        url: string | null;
        label: string;
        active: boolean;
    }[];
    onPageChange: (url: string) => void;
}>();

function handlePageChange(url: string | null) {
    if (url) {
        props.onPageChange(url);
    }
}
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
                class="border-neo-dark border-3 bg-white px-3 py-2 text-xs leading-4 text-gray-400 opacity-50 sm:px-4 sm:py-3 sm:text-sm"
                v-html="link.label"
            />
            <button
                v-else
                type="button"
                class="border-neo-dark hover:bg-primary focus-visible:ring-primary cursor-pointer border-3 px-3 py-2 text-xs leading-4 transition-all hover:text-white focus-visible:ring-2 focus-visible:ring-offset-1 focus-visible:outline-none sm:px-4 sm:py-3 sm:text-sm"
                :class="{
                    'bg-primary hover:bg-primary text-white': link.active,
                    'bg-white': !link.active,
                }"
                @click="handlePageChange(link.url)"
                :aria-current="link.active ? 'page' : undefined"
                v-html="link.label"
            />
        </template>
    </nav>
</template>
