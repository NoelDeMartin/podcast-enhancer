<script setup lang="ts">
import { computed } from 'vue';
import type { Component } from 'vue';

const props = defineProps<{
    title: string;
    description: string;
    icon: Component;
    iconClass?: string;
    iconWrapperClass?: string;
    wrapperClass?: string;
}>();

const resolvedIconWrapperClass = computed(() => {
    if (props.iconWrapperClass) return props.iconWrapperClass;
    if (!props.iconClass) return 'bg-neo-yellow';

    // Keep callsites simple: `text-neo-blue` becomes `bg-neo-blue` for the badge.
    return props.iconClass.replaceAll(/\btext-/g, 'bg-');
});
</script>

<template>
    <div
        :class="[
            'group border-neo-dark bg-neo-bg text-neo-dark border-3 p-4 transition-shadow duration-300 sm:p-8',
            wrapperClass ?? 'hover:shadow-neo-hard',
        ]"
    >
        <div class="flex items-start justify-between gap-6">
            <div>
                <h4 class="text-xl font-black tracking-tight sm:text-2xl">
                    {{ title }}
                </h4>
                <p class="text-neo-dark/70 mt-3 max-w-md text-xs font-bold sm:text-sm">
                    {{ description }}
                </p>
            </div>

            <div
                :class="[
                    'border-neo-dark shadow-neo-hard relative grid size-10 shrink-0 rotate-6 place-items-center border-3 transition-transform duration-300 group-hover:rotate-0 sm:size-14',
                    resolvedIconWrapperClass,
                ]"
            >
                <component :is="icon" class="size-6 text-white sm:size-8" />
            </div>
        </div>
    </div>
</template>
