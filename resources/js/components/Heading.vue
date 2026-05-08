<script setup lang="ts">
import { computed } from 'vue';

import { cn } from '@/lib/utils';

type Props = {
    title: string;
    description?: string;
    variant?: 'default' | 'small';
    class?: string;
};

const props = withDefaults(defineProps<Props>(), {
    variant: 'default',
});

const className = computed(() => props.class);
</script>

<template>
    <header :class="cn(variant === 'small' ? '' : 'mb-8 space-y-0.5', className)">
        <h2
            :class="
                variant === 'small'
                    ? 'mb-0.5 text-base font-medium'
                    : 'text-xl font-semibold tracking-tight'
            "
        >
            {{ title }}
        </h2>
        <p v-if="description" class="text-muted-foreground text-sm">
            {{ description }}
        </p>
    </header>
</template>
