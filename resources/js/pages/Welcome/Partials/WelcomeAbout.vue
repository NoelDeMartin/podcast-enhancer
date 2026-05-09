<script setup lang="ts"></script>

<template>
    <section id="who-made-this" class="py-16 sm:py-24">
        <div class="mx-auto max-w-7xl px-4 sm:px-6">
            <h2
                class="border-neo-dark bg-neo-pink shadow-neo-hard inline-block -rotate-2 border-3 px-4 py-2 text-2xl font-black tracking-tighter uppercase transition-transform duration-300 hover:rotate-0 sm:text-5xl"
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
                        class="border-neo-dark shadow-neo-hard aspect-square w-full border-3 object-cover"
                        loading="lazy"
                        decoding="async"
                    />
                </div>

                <div class="flex h-full flex-col text-lg leading-relaxed font-bold sm:text-2xl">
                    <div
                        class="border-neo-dark hover:shadow-neo-hard-blue flex h-full flex-col border-3 bg-white p-4 transition-shadow duration-300 sm:p-8"
                    >
                        <p class="text-pretty">
                            My name is Noel, and I'm a developer and entrepreneur based in
                            Barcelona. If you want to learn more about me, check out
                            <a
                                href="https://noeldemartin.com"
                                target="_blank"
                                class="group text-neo-blue relative isolate inline-flex items-center gap-1 no-underline focus-visible:outline-none"
                            >
                                <span class="relative z-10">my personal website</span>
                                <i-carbon-launch class="relative z-10 size-5" />
                                <div
                                    aria-hidden="true"
                                    class="bg-neo-blue pointer-events-none absolute inset-0 z-20 [clip-path:inset(calc(100%-3px)_0_0_0)] [transition:clip-path_200ms_ease-out] group-hover:[clip-path:inset(0_0_0_0)] group-focus-visible:[clip-path:inset(0_0_0_0)]"
                                >
                                    <div class="absolute inset-0 inline-flex items-center gap-1">
                                        <span class="whitespace-nowrap text-white"
                                            >my personal website</span
                                        >
                                        <i-carbon-launch class="size-5 shrink-0 text-white" />
                                    </div>
                                </div> </a
                            >!
                        </p>

                        <p class="text-neo-dark/70 mt-auto pt-6 text-base font-bold md:text-right">
                            P.S. If you've got some feedback, make sure to let me know.
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </section>
</template>
