<script setup lang="ts">
import { Link, usePage } from '@inertiajs/vue3';
import { onBeforeUnmount, onMounted, ref } from 'vue';
import { Button } from '@/components/ui/button';
import { dashboard, login } from '@/routes';
import type { Auth } from '@/types';
import WelcomeNavLink from './WelcomeNavLink.vue';

const page = usePage<{ auth: Auth }>();

const showNavbar = ref(false);
let rafId: number | null = null;

function updateNavbarVisibility() {
    const threshold = window.innerHeight * 0.3;
    showNavbar.value = window.scrollY > threshold;
}

function onScrollOrResize() {
    if (rafId !== null) return;

    rafId = window.requestAnimationFrame(() => {
        rafId = null;
        updateNavbarVisibility();
    });
}

onMounted(() => {
    updateNavbarVisibility();
    window.addEventListener('scroll', onScrollOrResize, { passive: true });
    window.addEventListener('resize', onScrollOrResize, { passive: true });
});

onBeforeUnmount(() => {
    window.removeEventListener('scroll', onScrollOrResize);
    window.removeEventListener('resize', onScrollOrResize);
    if (rafId !== null) window.cancelAnimationFrame(rafId);
});
</script>

<template>
    <nav
        class="fixed top-0 left-0 right-0 z-50 border-b-3 border-neo-dark bg-neo-bg px-4 py-2 transition-all duration-400 ease-out sm:px-6 sm:py-4"
        :class="
            showNavbar
                ? 'translate-y-0 pointer-events-auto'
                : '-translate-y-full pointer-events-none'
        "
    >
        <div class="mx-auto flex max-w-7xl items-center justify-between">
            <Link
                href="#"
                v-scroll-on-click
                class="text-lg font-black uppercase tracking-tighter sm:text-2xl"
            >
                Podcast <span class="text-neo-pink">Enhancer</span>
            </Link>

            <div class="hidden items-center gap-8 md:flex">
                <WelcomeNavLink href="#how-it-works">How does it work?</WelcomeNavLink>
                <WelcomeNavLink href="#get-started">Get Started</WelcomeNavLink>
                <WelcomeNavLink href="#who-made-this">Who made this?</WelcomeNavLink>
            </div>

            <div class="flex items-center gap-4">
                <Button as-child size="sm">
                    <Link v-if="page.props.auth.user" :href="dashboard()">Dashboard</Link>
                    <Link v-else :href="login()">Log In</Link>
                </Button>
            </div>
        </div>
    </nav>
</template>
