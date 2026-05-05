<script setup lang="ts">
import { Head, Link, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';

import { Button } from '@/components/ui/button';
import WelcomeNavLink from '@/components/WelcomeNavLink.vue';
import AppLayout from '@/layouts/AppLayout.vue';
import { dashboard, home, login } from '@/routes';

import WelcomeAbout from './Partials/WelcomeAbout.vue';
import WelcomeGetStarted from './Partials/WelcomeGetStarted.vue';
import WelcomeHero from './Partials/WelcomeHero.vue';
import WelcomeManual from './Partials/WelcomeManual.vue';

const page = usePage();
const auth = computed(() => page.props.auth);
</script>

<template>
    <Head title="Podcast Enhancer - Improve your podcast experience" />

    <AppLayout floating-header :logo-url="null">
        <template #header>
            <nav class="hidden items-center gap-8 md:flex">
                <WelcomeNavLink :href="`${home().url}#how-it-works`"
                    >How does it work?</WelcomeNavLink
                >
                <WelcomeNavLink :href="`${home().url}#get-started`">Get Started</WelcomeNavLink>
                <WelcomeNavLink :href="`${home().url}#who-made-this`"
                    >Who made this?</WelcomeNavLink
                >
            </nav>
        </template>
        <template #header-right>
            <Button as-child size="sm" variant="secondary">
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
