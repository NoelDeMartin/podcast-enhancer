<script setup lang="ts">
import type { HTMLAttributes, Component } from 'vue';

import { cn } from '@/lib/utils';

interface Props {
    as?: string | Component;
    class?: HTMLAttributes['class'];
}

const props = withDefaults(defineProps<Props>(), {
    as: 'div',
});
</script>

<template>
    <component
        :is="as"
        data-slot="neo-card"
        :class="
            cn(
                'bg-white text-black border-4 border-black shadow-[8px_8px_0_0_#000] p-8 sm:p-10 md:p-14',
                props.class,
            )
        "
    >
        <slot />
    </component>
</template>
