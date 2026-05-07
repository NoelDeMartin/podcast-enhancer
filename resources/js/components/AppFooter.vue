<script setup lang="ts">
import Email from '~icons/carbon/email';
import LogoGithub from '~icons/carbon/logo-github';

import TextLink from '@/components/TextLink.vue';

const currentYear = new Date().getFullYear();
</script>

<template>
    <footer class="bg-neo-dark py-4 text-white">
        <div
            class="mx-auto flex max-w-7xl flex-col items-center justify-between gap-6 px-4 sm:px-6 md:flex-row"
        >
            <p class="text-sm font-bold opacity-70">
                &copy; {{ currentYear }}
                <a
                    href="https://noeldemartin.com"
                    target="_blank"
                    class="hover:text-primary hover:underline"
                    >Noel De Martin</a
                >
            </p>

            <div class="flex items-center gap-8">
                <a
                    href="https://github.com/noeldemartin/podcasts-enhancer"
                    target="_blank"
                    rel="noreferrer"
                    class="group hover:text-primary flex items-center gap-2 text-sm font-black tracking-widest uppercase transition-colors"
                >
                    <LogoGithub class="size-5" />
                    GitHub
                </a>
                <a
                    href="mailto:hey@noeldemartin.com?subject=Podcast+Enhancer"
                    class="group hover:text-primary flex items-center gap-2 text-sm font-black tracking-widest uppercase transition-colors"
                >
                    <Email class="size-5" />
                    Contact
                </a>
            </div>
        </div>
    </footer>
</template>
