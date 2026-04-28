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
            'group border-3 border-neo-dark bg-neo-bg p-8 text-neo-dark transition-shadow duration-300',
            wrapperClass ?? 'hover:shadow-neo-hard',
        ]"
    >
        <div class="flex items-start justify-between gap-6">
            <div>
                <h4 class="text-2xl font-black tracking-tight">
                    {{ title }}
                </h4>
                <p class="mt-3 max-w-md text-sm font-bold text-neo-dark/70">
                    {{ description }}
                </p>
            </div>

            <div
                :class="[
                    'relative grid h-14 w-14 shrink-0 place-items-center border-3 border-neo-dark shadow-neo-hard rotate-2 transition-transform duration-300 group-hover:-rotate-2',
                    resolvedIconWrapperClass,
                ]"
            >
                <component :is="icon" class="size-8 text-white" />
            </div>
        </div>
    </div>
</template>
