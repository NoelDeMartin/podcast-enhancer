<script setup lang="ts">
import ExternalLink from '~icons/lucide/external-link';
</script>

<template>
    <section id="who-made-this" class="border-b-3 border-neo-dark px-6 py-24">
        <div class="mx-auto max-w-7xl">
            <h2
                class="inline-block border-3 border-neo-dark bg-neo-pink px-4 py-2 text-3xl font-black uppercase tracking-tighter shadow-neo-hard sm:text-5xl -rotate-2 hover:rotate-0 transition-transform duration-300"
            >
                Who made this?
            </h2>

            <div
                class="mt-12 grid grid-cols-1 items-start gap-10 md:grid-cols-[320px_1fr] md:items-stretch px-8"
            >
                <div class="mx-auto w-full max-w-xs md:mx-0 md:max-w-none">
                    <img
                        src="/img/avatar.jpg"
                        alt="Noel"
                        class="aspect-square w-full border-3 border-neo-dark object-cover shadow-neo-hard"
                        loading="lazy"
                        decoding="async"
                    />
                </div>

                <div class="flex h-full flex-col text-xl font-bold leading-relaxed sm:text-2xl">
                    <div
                        class="flex h-full flex-col border-3 border-neo-dark bg-white p-6 hover:shadow-neo-hard-blue transition-shadow duration-300 sm:p-8"
                    >
                        <p class="text-pretty">
                            My name is Noel, and I'm a developer and entrepreneur based in
                            Barcelona. If you want to learn more about me, check out
                            <a
                                href="https://noeldemartin.com"
                                target="_blank"
                                class="inline-flex items-center gap-1 border-b-3 border-neo-blue text-neo-blue hover:bg-neo-blue hover:text-white transition-all"
                            >
                                my personal website
                                <ExternalLink class="size-5" /> </a
                            >!
                        </p>

                        <p class="mt-auto pt-6 text-right text-base font-bold text-neo-dark/70">
                            P.S. If you've got some feedback, make sure to let me know.
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </section>
</template>
