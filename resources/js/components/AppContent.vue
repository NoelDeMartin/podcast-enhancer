<script setup lang="ts">
import { computed } from 'vue';

type Props = {
    class?: string;
};

const props = defineProps<Props>();
const className = computed(() => props.class);
</script>

<template>
    <main
        :class="[
            'mx-auto flex h-full w-full max-w-none flex-1 flex-col gap-0 rounded-none has-data-floating-player:pb-14',
            className,
        ]"
    >
        <slot />
    </main>
</template>
