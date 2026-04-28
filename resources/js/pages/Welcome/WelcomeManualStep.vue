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
                `sm:relative sm:after:absolute sm:after:left-7 sm:after:top-1/2 sm:after:translate-y-7 sm:after:-bottom-20 sm:after:border-l-3 sm:after:border-dashed sm:after:border-neo-dark sm:after:content-['']`,
        ]"
    >
        <div class="relative flex flex-col items-center">
            <div
                class="relative z-10 flex h-14 w-14 items-center justify-center border-3 border-neo-dark bg-neo-yellow text-2xl font-black shadow-neo-hard"
            >
                {{ number }}
            </div>
        </div>

        <div
            :class="[
                'group relative border-3 border-neo-dark bg-neo-bg p-8 pr-24 transition-shadow duration-300',
                wrapperClass ?? 'hover:shadow-neo-hard',
            ]"
        >
            <div
                :class="[
                    'absolute top-8 right-8 flex h-16 w-16 items-center justify-center border-3 border-neo-dark text-white shadow-neo-hard rotate-3 transition-transform duration-300 group-hover:-rotate-3 group-hover:translate-x-1 group-hover:translate-y-1',
                    iconWrapperClass ?? 'bg-neo-blue',
                ]"
            >
                <component :is="icon" class="size-10" />
            </div>

            <h3 class="text-2xl font-black uppercase tracking-tight">
                {{ title }}
            </h3>
            <p class="mt-4 font-bold text-neo-dark/70">
                {{ description }}
            </p>
        </div>
    </div>
</template>
