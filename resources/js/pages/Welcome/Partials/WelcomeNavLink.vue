<script setup lang="ts">
defineProps<{
    href: string;
}>();
</script>

<template>
    <a
        :href="href"
        v-scroll-on-click
        class="group hover:text-primary relative inline-flex font-mono text-sm font-bold tracking-widest uppercase transition-colors focus-visible:outline-none"
    >
        <slot />
        <div
            class="bg-primary absolute right-0 -bottom-1 left-0 h-[3px] origin-right scale-x-0 transition-transform duration-200 ease-out group-hover:origin-left group-hover:scale-x-100 group-focus-visible:origin-left group-focus-visible:scale-x-100"
        />
    </a>
</template>
