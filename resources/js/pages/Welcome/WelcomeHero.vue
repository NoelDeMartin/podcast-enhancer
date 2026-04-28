<script setup lang="ts">
import { ref } from 'vue';
import { Button } from '@/components/ui/button';

const el = ref<HTMLElement | null>(null);

defineExpose({
    getEl: () => el.value,
});
</script>

<template>
    <header ref="el" class="relative overflow-visible">
        <div class="w-full bg-neo-yellow px-6 pt-20 pb-24 lg:pt-32 lg:pb-28">
            <div class="mx-auto max-w-7xl">
                <div class="max-w-3xl">
                    <h1
                        class="flex flex-col items-start text-balance uppercase text-5xl font-black leading-[0.9] tracking-tighter sm:text-7xl md:text-8xl"
                    >
                        <span>It's time to upgrade your</span>
                        <span
                            class="bg-neo-dark text-neo-yellow px-2 -rotate-3 text-9xl my-2 hover:rotate-0 transition-transform duration-200"
                            >podcast</span
                        >
                        <span>experience.</span>
                    </h1>
                    <p
                        class="mt-8 text-pretty text-xl font-bold leading-relaxed text-neo-dark/80 sm:text-2xl"
                    >
                        There are many shows you love, but not enough time. Improve your feeds with
                        transcriptions, chapters, and more.

                        <br />
                        <br />

                        All while keeping your favorite podcast app!
                    </p>
                    <div class="mt-12">
                        <Button as-child size="lg" class="h-16 px-10 text-xl">
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
