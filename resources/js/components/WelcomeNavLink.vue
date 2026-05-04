<script setup lang="ts">
defineProps<{
    href: string;
}>();
</script>

<template>
    <a
        :href="href"
        v-scroll-on-click
        class="group relative inline-flex text-sm font-bold uppercase tracking-widest transition-colors hover:text-neo-pink focus-visible:outline-none"
    >
        <slot />
        <div
            class="absolute -bottom-1 left-0 right-0 h-[3px] origin-right scale-x-0 bg-neo-pink transition-transform duration-200 ease-out group-hover:origin-left group-hover:scale-x-100 group-focus-visible:origin-left group-focus-visible:scale-x-100"
        />
    </a>
</template>
