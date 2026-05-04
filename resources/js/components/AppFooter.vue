<script setup lang="ts">
import LogoGithub from '~icons/carbon/logo-github';
import Email from '~icons/carbon/email';

const currentYear = new Date().getFullYear();
</script>

<template>
    <footer class="px-4 py-4 sm:px-6 bg-neo-dark text-white">
        <div class="mx-auto flex max-w-7xl flex-col items-center justify-between gap-6 md:flex-row">
            <p class="text-sm font-bold opacity-70">&copy; {{ currentYear }} Noel De Martin</p>

            <div class="flex items-center gap-8">
                <a
                    href="https://github.com/noeldemartin/podcasts-enhancer"
                    target="_blank"
                    rel="noreferrer"
                    class="group flex items-center gap-2 text-sm font-black uppercase tracking-widest transition-colors hover:text-neo-pink"
                >
                    <LogoGithub class="size-5" />
                    GitHub
                </a>
                <a
                    href="mailto:hey@noeldemartin.com?subject=Podcast+Enhancer"
                    class="group flex items-center gap-2 text-sm font-black uppercase tracking-widest transition-colors hover:text-neo-pink"
                >
                    <Email class="size-5" />
                    Contact
                </a>
            </div>
        </div>
    </footer>
</template>
