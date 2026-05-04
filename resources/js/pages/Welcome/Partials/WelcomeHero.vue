<script setup lang="ts">
import { Link, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';
import { Button } from '@/components/ui/button';
import { dashboard } from '@/routes';

const page = usePage();
const auth = computed(() => page.props.auth);
</script>

<template>
    <header class="relative overflow-visible">
        <div class="w-full bg-neo-yellow pt-16 pb-12 sm:pt-20 sm:pb-24 lg:pt-32 lg:pb-28">
            <div class="mx-auto max-w-7xl px-4 sm:px-6">
                <div class="max-w-3xl">
                    <h1
                        class="flex flex-col items-start text-balance uppercase text-3xl font-black leading-[0.9] tracking-tighter sm:text-7xl md:text-8xl"
                    >
                        <span>It's time to upgrade your</span>
                        <span
                            class="bg-neo-dark text-neo-yellow px-2 -rotate-3 text-5xl my-2 hover:rotate-0 transition-transform duration-200 sm:text-8xl md:text-9xl"
                            >podcast</span
                        >
                        <span>experience.</span>
                    </h1>
                    <p
                        v-if="!auth.user"
                        class="mt-8 text-pretty text-lg font-bold leading-relaxed text-neo-dark/80 sm:text-2xl"
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
                                class="text-pretty text-lg font-bold leading-relaxed text-neo-dark sm:text-2xl"
                            >
                                Welcome back, {{ auth.user.name }}!
                            </p>
                            <Button as-child size="lg" class="mt-6 h-16 px-10 text-xl sm:mt-8">
                                <Link :href="dashboard()">
                                    Open dashboard
                                    <i-carbon-arrow-right class="ml-2 size-6" />
                                </Link>
                            </Button>
                        </template>
                        <Button v-else as-child size="lg" class="h-16 px-10 text-xl">
                            <a href="#get-started" v-scroll-on-click>
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
            class="pointer-events-none block h-20 w-full fill-neo-yellow -mb-10"
            viewBox="0 0 100 100"
            preserveAspectRatio="none"
        >
            <path d="M0 0 L100 0 L0 100 Z" />
        </svg>
    </header>
</template>
