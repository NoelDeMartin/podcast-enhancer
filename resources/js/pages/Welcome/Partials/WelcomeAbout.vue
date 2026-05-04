<script setup lang="ts">
import ExternalLink from '~icons/lucide/external-link';
</script>

<template>
    <section id="who-made-this" class="py-16 sm:py-24">
        <div class="mx-auto max-w-7xl px-4 sm:px-6">
            <h2
                class="inline-block border-3 border-neo-dark bg-neo-pink px-4 py-2 text-2xl font-black uppercase tracking-tighter shadow-neo-hard sm:text-5xl -rotate-2 hover:rotate-0 transition-transform duration-300"
            >
                Who made this?
            </h2>

            <div
                class="mt-12 grid grid-cols-1 items-start gap-10 md:grid-cols-[320px_1fr] md:items-stretch md:px-8"
            >
                <div class="mx-auto w-full max-w-xs md:mx-0 md:max-w-none">
                    <img
                        src="@/assets/img/avatar.png"
                        alt=""
                        class="aspect-square w-full border-3 border-neo-dark object-cover shadow-neo-hard"
                        loading="lazy"
                        decoding="async"
                    />
                </div>

                <div class="flex h-full flex-col text-lg font-bold leading-relaxed sm:text-2xl">
                    <div
                        class="flex h-full flex-col border-3 border-neo-dark bg-white p-4 hover:shadow-neo-hard-blue transition-shadow duration-300 sm:p-8"
                    >
                        <p class="text-pretty">
                            My name is Noel, and I'm a developer and entrepreneur based in
                            Barcelona. If you want to learn more about me, check out
                            <a
                                href="https://noeldemartin.com"
                                target="_blank"
                                class="group isolate relative inline-flex items-center gap-1 text-neo-blue no-underline focus-visible:outline-none"
                            >
                                <span class="relative z-10">my personal website</span>
                                <ExternalLink class="relative z-10 size-5" />
                                <div
                                    aria-hidden="true"
                                    class="pointer-events-none absolute inset-0 bg-neo-blue [clip-path:inset(calc(100%-3px)_0_0_0)] [transition:clip-path_200ms_ease-out] z-20 group-hover:[clip-path:inset(0_0_0_0)] group-focus-visible:[clip-path:inset(0_0_0_0)]"
                                >
                                    <div class="absolute inset-0 inline-flex items-center gap-1">
                                        <span class="whitespace-nowrap text-white"
                                            >my personal website</span
                                        >
                                        <ExternalLink class="size-5 shrink-0 text-white" />
                                    </div>
                                </div> </a
                            >!
                        </p>

                        <p class="mt-auto pt-6 md:text-right text-base font-bold text-neo-dark/70">
                            P.S. If you've got some feedback, make sure to let me know.
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </section>
</template>
