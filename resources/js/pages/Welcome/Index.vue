<script setup lang="ts">
import { Head, Link, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';
import AppLayout from '@/layouts/AppLayout.vue';
import WelcomeNavLink from '@/components/WelcomeNavLink.vue';
import { Button } from '@/components/ui/button';
import { dashboard, login } from '@/routes';
import WelcomeHero from './Partials/WelcomeHero.vue';
import WelcomeManual from './Partials/WelcomeManual.vue';
import WelcomeGetStarted from './Partials/WelcomeGetStarted.vue';
import WelcomeAbout from './Partials/WelcomeAbout.vue';

const page = usePage();
const auth = computed(() => page.props.auth);
</script>

<template>
    <Head title="Podcast Enhancer - Improve your podcast experience" />

    <AppLayout :floating-header="true" :logo-url="null">
        <template #header>
            <div class="hidden items-center gap-8 md:flex">
                <WelcomeNavLink href="#how-it-works">How does it work?</WelcomeNavLink>
                <WelcomeNavLink href="#get-started">Get Started</WelcomeNavLink>
                <WelcomeNavLink href="#who-made-this">Who made this?</WelcomeNavLink>
            </div>
        </template>
        <template #header-right>
            <Button as-child size="sm">
                <Link v-if="auth.user" :href="dashboard()">Dashboard</Link>
                <Link v-else :href="login()">Log In</Link>
            </Button>
        </template>

        <WelcomeHero />
        <WelcomeManual />
        <WelcomeGetStarted />
        <WelcomeAbout />
    </AppLayout>
</template>
