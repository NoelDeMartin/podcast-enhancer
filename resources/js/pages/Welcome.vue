<script setup lang="ts">
import { Link, usePage } from '@inertiajs/vue3';
import { Star } from 'lucide-vue-next';
import { Card } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { login, dashboard } from '@/routes';

const page = usePage();
const githubUrl = 'https://github.com';
</script>

<template>
    <div class="min-h-svh bg-[#f6c2cf] p-6 sm:p-10">
        <Card class="mx-auto max-w-5xl p-6 sm:p-10">
            <div class="mx-auto max-w-3xl text-center">
                <h1
                    class="text-balance text-4xl font-black leading-[0.95] tracking-tight sm:text-5xl md:text-6xl"
                >
                    UPGRADE YOUR PODCASTS WITH AI.
                    <br />
                    KEEP YOUR FAVORITE PLAYER.
                </h1>

                <p class="mt-6 text-pretty text-base font-medium text-black/70 sm:text-lg">
                    Supercharge your listening experience. Generate smart summaries, clickable
                    chapters, and full transcripts injected directly into a custom RSS feed you can
                    use anywhere.
                </p>

                <div
                    class="mt-10 flex flex-col items-stretch justify-center gap-4 sm:flex-row sm:items-center"
                >
                    <Button as-child size="lg" class="bg-[#d5ff00]">
                        <Link v-if="page.props.auth.user" :href="dashboard()">Open Dashboard</Link>
                        <Link v-else :href="login()">Log In</Link>
                    </Button>

                    <Button as-child size="lg" variant="outline" class="bg-white">
                        <a :href="githubUrl" target="_blank" rel="noreferrer">
                            <Star class="mr-1.5 size-4" />
                            Star on GitHub
                        </a>
                    </Button>
                </div>
            </div>
        </Card>
    </div>
</template>
