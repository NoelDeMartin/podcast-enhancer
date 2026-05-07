<script setup lang="ts">
import Moon from '~icons/carbon/moon';
import Screen from '~icons/carbon/screen';
import Sun from '~icons/carbon/sun';

import { useAppearance } from '@/composables/useAppearance';

const { appearance, updateAppearance } = useAppearance();

const tabs = [
    { value: 'light', Icon: Sun, label: 'Light' },
    { value: 'dark', Icon: Moon, label: 'Dark' },
    { value: 'system', Icon: Screen, label: 'System' },
] as const;
</script>

<template>
    <div class="inline-flex gap-1 rounded-none bg-neutral-100 p-1 dark:bg-neutral-800">
        <button
            v-for="{ value, Icon, label } in tabs"
            :key="value"
            @click="updateAppearance(value)"
            :class="[
                'flex items-center rounded-none px-3.5 py-1.5 transition-colors',
                appearance === value
                    ? 'shadow-neo-hard bg-white dark:bg-neutral-700 dark:text-neutral-100'
                    : 'text-neutral-500 hover:bg-neutral-200/60 hover:text-black dark:text-neutral-400 dark:hover:bg-neutral-700/60',
            ]"
        >
            <component :is="Icon" class="-ml-1 size-4" />
            <span class="ml-1.5 text-sm">{{ label }}</span>
        </button>
    </div>
</template>
