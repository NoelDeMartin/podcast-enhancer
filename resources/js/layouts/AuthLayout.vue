<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import { computed } from 'vue';

import AppLogo from '@/components/AppLogo.vue';
import { home } from '@/routes';

const props = defineProps<{
    title?: string;
    description?: string;
    logoUrl?: string | null;
}>();

const logoHref = computed(() => (props.logoUrl === null ? null : (props.logoUrl ?? home())));
</script>

<template>
    <div class="flex min-h-svh flex-col items-center justify-center px-4 py-16 sm:px-6">
        <main class="flex w-full justify-center">
            <div class="w-full">
                <header class="pb-12 text-center sm:pb-14">
                    <div class="flex flex-col items-center gap-6">
                        <Link
                            v-if="logoHref !== null"
                            :href="logoHref"
                            class="group inline-flex focus-visible:outline-none"
                        >
                            <AppLogo />
                        </Link>
                        <span v-else class="group inline-flex focus-visible:outline-none">
                            <AppLogo />
                        </span>

                        <div v-if="title || description" class="mx-auto max-w-lg">
                            <h1
                                v-if="title"
                                class="text-neo-dark font-grotesk text-[1.6rem] leading-none font-black tracking-wide text-balance sm:text-3xl"
                            >
                                {{ title }}
                            </h1>
                            <p
                                v-if="description"
                                class="text-neo-dark/80 mt-2 text-sm font-bold text-balance"
                            >
                                {{ description }}
                            </p>
                        </div>
                    </div>
                </header>

                <div
                    class="border-neo-dark bg-neo-bg shadow-neo-hard mx-auto max-w-sm border-3 p-10 sm:p-12"
                >
                    <slot />
                </div>
            </div>
        </main>
    </div>
</template>
