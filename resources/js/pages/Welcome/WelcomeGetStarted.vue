<script setup lang="ts">
import { Button } from '@/components/ui/button';
</script>

<template>
    <section id="get-started" class="px-4 py-16 sm:px-6 sm:py-24">
        <div class="mx-auto max-w-7xl">
            <h2
                class="inline-block border-3 border-neo-dark bg-neo-blue px-4 py-2 text-2xl font-black uppercase tracking-tighter text-white shadow-neo-hard sm:text-5xl -rotate-2 hover:rotate-0 transition-transform duration-300"
            >
                Get Started
            </h2>

            <div class="mt-20 grid gap-10 lg:grid-cols-2 lg:items-stretch lg:px-8">
                <div
                    class="group relative border-3 border-neo-dark bg-neo-yellow text-neo-dark transition-shadow duration-300 hover:shadow-neo-hard"
                >
                    <div class="flex h-full flex-col p-3 sm:p-10">
                        <div class="flex items-center justify-between gap-6">
                            <span
                                class="inline-flex items-center gap-2 border-3 border-neo-dark bg-neo-bg px-2 py-1 text-[10px] font-black uppercase tracking-wide sm:tracking-widest sm:text-xs"
                            >
                                Invite-only
                            </span>

                            <div
                                class="flex h-10 w-10 items-center justify-center border-3 border-neo-dark bg-neo-dark text-neo-yellow shadow-neo-hard rotate-3 transition-transform duration-300 group-hover:-rotate-3 group-hover:translate-x-1 group-hover:translate-y-1 sm:h-14 sm:w-14"
                            >
                                <i-carbon-cloud class="size-6 sm:size-8" />
                            </div>
                        </div>

                        <div class="mt-8">
                            <h3 class="text-lg font-black uppercase tracking-tight sm:text-5xl">
                                Create an account
                            </h3>
                            <p class="mt-3 text-base font-bold text-neo-dark/80 sm:text-lg">
                                Use it in the cloud, forget about headaches.
                            </p>
                        </div>

                        <div class="mt-8 border-t-3 border-neo-dark pt-6">
                            <p class="text-sm font-bold sm:text-base">
                                The app is currently invite-only, but let me know if you want to
                                give it a try!
                            </p>

                            <ul class="mt-6 space-y-3 text-sm font-bold sm:text-base">
                                <li class="flex gap-3">
                                    <i-carbon-checkmark class="mt-1 size-5 shrink-0" />
                                    No server setup
                                </li>
                                <li class="flex gap-3">
                                    <i-carbon-checkmark class="mt-1 size-5 shrink-0" />
                                    No API Keys needed
                                </li>
                            </ul>
                        </div>

                        <div class="mt-auto pt-10">
                            <Button
                                as-child
                                class="h-14 w-full bg-neo-dark px-4 text-lg text-white sm:px-8"
                            >
                                <a href="mailto:hey@noeldemartin.com?subject=Podcast+Enhancer">
                                    <i-carbon-email class="mr-2 size-6" />
                                    Request an invite
                                </a>
                            </Button>
                        </div>
                    </div>
                </div>

                <div
                    class="group flex h-full flex-col border-3 border-neo-dark bg-neo-bg p-3 text-neo-dark transition-shadow duration-300 hover:shadow-neo-hard-pink sm:p-10"
                >
                    <div class="flex items-center justify-between gap-6">
                        <span
                            class="inline-flex items-center gap-2 border-3 border-neo-dark bg-white px-2 py-1 text-[10px] font-black uppercase tracking-wide sm:tracking-widest sm:text-xs"
                        >
                            DIY
                        </span>

                        <div
                            class="flex h-10 w-10 items-center justify-center border-3 border-neo-dark bg-neo-blue text-white shadow-neo-hard rotate-3 transition-transform duration-300 group-hover:-rotate-3 group-hover:translate-x-1 group-hover:translate-y-1 sm:h-14 sm:w-14"
                        >
                            <i-carbon-bare-metal-server class="size-6 sm:size-8" />
                        </div>
                    </div>

                    <div class="mt-8">
                        <h3 class="text-lg font-black uppercase tracking-tight sm:text-5xl">
                            Self-Host
                        </h3>
                        <p class="mt-3 text-base font-bold text-neo-dark/80 sm:text-lg">
                            Run it in your hardware, keep full control.
                        </p>
                    </div>

                    <div class="mt-8 border-t-3 border-neo-dark pt-6">
                        <p class="text-sm font-bold sm:text-base">
                            Use your own API keys via Mistral, Gemini, Anthropic, or any other
                            provider supported by
                            <a
                                class="underline decoration-2 underline-offset-4 hover:decoration-neo-pink"
                                href="https://laravel.com/docs/ai-sdk"
                                target="_blank"
                                rel="noreferrer"
                                >the Laravel AI SDK</a
                            >.
                        </p>

                        <ul class="mt-6 space-y-3 text-sm font-bold sm:text-base">
                            <li class="flex gap-3">
                                <i-carbon-checkmark class="mt-1 size-5 shrink-0" />
                                100% open source
                            </li>
                        </ul>
                    </div>

                    <div class="mt-auto pt-10">
                        <Button as-child class="h-14 w-full border-3 px-4 text-lg sm:px-8">
                            <a
                                href="https://github.com/noeldemartin/podcasts-enhancer"
                                target="_blank"
                                rel="noreferrer"
                            >
                                <i-carbon-logo-github class="mr-2 size-6" />
                                View on GitHub
                            </a>
                        </Button>
                    </div>
                </div>
            </div>
        </div>
    </section>
</template>
