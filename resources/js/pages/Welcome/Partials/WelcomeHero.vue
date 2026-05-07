<script setup lang="ts">
import { Link, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';

import { Button } from '@/components/ui/button';
import { dashboard, home } from '@/routes';

const page = usePage();
const auth = computed(() => page.props.auth);
</script>

<template>
    <header class="relative overflow-visible">
        <div class="bg-neo-yellow w-full pt-16 pb-12 sm:pt-20 sm:pb-24 lg:pt-32 lg:pb-28">
            <div class="mx-auto max-w-7xl px-4 sm:px-6">
                <div class="max-w-3xl">
                    <h1
                        class="flex flex-col items-start text-3xl leading-[0.9] font-black tracking-tighter text-balance uppercase sm:text-7xl md:text-8xl"
                    >
                        <span>It's time to upgrade your</span>
                        <span
                            class="bg-neo-dark text-neo-yellow my-2 -rotate-3 px-2 text-5xl transition-transform duration-200 hover:rotate-0 sm:text-8xl md:text-9xl"
                            >podcast</span
                        >
                        <span>experience.</span>
                    </h1>
                    <p
                        v-if="!auth.user"
                        class="text-neo-dark/80 mt-8 text-lg leading-relaxed font-bold text-pretty sm:text-2xl"
                    >
                        There are many shows you love, but not enough time. Improve your feeds with
                        transcriptions, chapters, and more.

                        <br />
                        <br />

                        All while keeping your favorite podcast app!
                    </p>

                    <div :class="auth.user ? 'mt-8 sm:mt-10' : 'mt-12'">
                        <template v-if="auth.user">
                            <p
                                class="text-neo-dark text-lg leading-relaxed font-bold text-pretty sm:text-2xl"
                            >
                                Welcome back, {{ auth.user.name }}!
                            </p>
                            <Button
                                as-child
                                size="lg"
                                class="mt-6 h-16 px-10 text-xl sm:mt-8"
                                variant="outline"
                            >
                                <Link :href="dashboard()">
                                    Open dashboard
                                    <i-carbon-arrow-right class="ml-2 size-6" />
                                </Link>
                            </Button>
                        </template>
                        <Button
                            v-else
                            as-child
                            size="lg"
                            variant="outline"
                            class="h-16 px-10 text-xl"
                        >
                            <a :href="`${home().url}#get-started`" v-scroll-on-click>
                                Get started
                                <i-carbon-arrow-right class="ml-2 size-6" />
                            </a>
                        </Button>
                    </div>
                </div>
            </div>
        </div>

        <svg
            aria-hidden="true"
            class="fill-neo-yellow pointer-events-none -mb-10 block h-20 w-full"
            viewBox="0 0 100 100"
            preserveAspectRatio="none"
        >
            <path d="M0 0 L100 0 L0 100 Z" />
        </svg>
    </header>
</template>
