<script setup lang="ts">
import type { Component } from 'vue';

defineProps<{
    number: number | string;
    title: string;
    description: string;
    icon: Component;
    iconWrapperClass?: string;
    wrapperClass?: string;
    last?: boolean;
}>();
</script>

<template>
    <div
        :class="[
            'grid items-start gap-6 sm:grid-cols-[3.5rem_1fr] sm:items-center sm:gap-10',
            !last &&
                `sm:after:border-neo-dark sm:relative sm:after:absolute sm:after:top-1/2 sm:after:-bottom-20 sm:after:left-7 sm:after:translate-y-7 sm:after:border-l-3 sm:after:border-dashed sm:after:content-['']`,
        ]"
    >
        <div class="relative flex flex-col items-center">
            <div
                class="border-neo-dark bg-neo-yellow shadow-neo-hard relative z-10 flex size-14 items-center justify-center border-3 font-mono text-2xl font-black"
            >
                {{ number }}
            </div>
        </div>

        <div
            :class="[
                'group border-neo-dark bg-neo-bg relative border-3 p-4 pr-16 transition-shadow duration-300 sm:p-8 sm:pr-24',
                wrapperClass ?? 'hover:shadow-neo-hard',
            ]"
        >
            <div
                :class="[
                    'border-neo-dark shadow-neo-hard absolute top-4 right-4 flex size-10 rotate-6 items-center justify-center border-3 text-white transition-transform duration-300 group-hover:translate-x-1 group-hover:translate-y-1 group-hover:rotate-0 sm:top-8 sm:right-8 sm:size-16',
                    iconWrapperClass ?? 'bg-neo-blue',
                ]"
            >
                <component :is="icon" class="size-6 sm:size-10" />
            </div>

            <h3 class="text-2xl font-black tracking-tight uppercase">
                {{ title }}
            </h3>
            <p class="text-neo-dark/70 mt-4 font-bold">
                {{ description }}
            </p>
        </div>
    </div>
</template>
