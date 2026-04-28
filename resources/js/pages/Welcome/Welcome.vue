<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import WelcomeNav from './WelcomeNav.vue';
import WelcomeHero from './WelcomeHero.vue';
import WelcomeManual from './WelcomeManual.vue';
import WelcomeGetStarted from './WelcomeGetStarted.vue';
import WelcomeAbout from './WelcomeAbout.vue';
</script>

<template>
    <Head title="Podcast Enhancer - Improve your podcast experience" />

    <div class="min-h-svh bg-neo-dots text-neo-dark selection:bg-neo-pink selection:text-white">
        <WelcomeNav />
        <WelcomeHero />
        <WelcomeManual />
        <WelcomeGetStarted />
        <WelcomeAbout />
    </div>
</template>

<style scoped>
html {
    scroll-behavior: smooth;
}
</style>
