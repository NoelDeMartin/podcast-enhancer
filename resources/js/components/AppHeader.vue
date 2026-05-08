<script setup lang="ts">
import { Link, usePage } from '@inertiajs/vue3';
import { computed, onBeforeUnmount, onMounted, ref } from 'vue';

import AppLogo from '@/components/AppLogo.vue';
import { Button } from '@/components/ui/button';
import UserMenu from '@/components/UserMenu.vue';
import { dashboard, login } from '@/routes';

const props = defineProps<{
    floating?: boolean;
    logoUrl?: string | null;
}>();

const page = usePage();
const auth = computed(() => page.props.auth);
const logoHref = computed(() => (props.logoUrl === null ? null : (props.logoUrl ?? dashboard())));
const show = ref(false);

let rafId: number | null = null;

function updateVisibility() {
    const threshold = window.innerHeight * 0.3;
    show.value = window.scrollY > threshold;
}

function onScrollOrResizeUpdateVisibility() {
    if (rafId !== null) {
        return;
    }

    rafId = window.requestAnimationFrame(() => {
        rafId = null;
        updateVisibility();
    });
}

onMounted(() => {
    if (!props.floating) {
        return;
    }

    updateVisibility();
    window.addEventListener('scroll', onScrollOrResizeUpdateVisibility, { passive: true });
    window.addEventListener('resize', onScrollOrResizeUpdateVisibility, { passive: true });
});

onBeforeUnmount(() => {
    if (!props.floating) {
        return;
    }

    window.removeEventListener('scroll', onScrollOrResizeUpdateVisibility);
    window.removeEventListener('resize', onScrollOrResizeUpdateVisibility);

    if (rafId !== null) {
        window.cancelAnimationFrame(rafId);
    }
});
</script>

<template>
    <header
        class="border-neo-dark bg-neo-bg border-b-3"
        :class="[
            props.floating
                ? 'fixed top-0 right-0 left-0 z-50 border-b-3 transition-all duration-400 ease-out'
                : '',
            props.floating
                ? show
                    ? 'pointer-events-auto translate-y-0'
                    : 'pointer-events-none -translate-y-full'
                : '',
        ]"
    >
        <div class="mx-auto flex h-16 max-w-7xl items-center justify-between px-4 sm:px-6">
            <div class="flex items-center gap-4">
                <Link v-if="logoHref !== null" :href="logoHref" class="flex items-center">
                    <AppLogo />
                </Link>
                <span v-else class="flex items-center">
                    <AppLogo />
                </span>
            </div>

            <div v-if="$slots.default" class="flex items-center gap-4">
                <slot />
            </div>

            <div class="flex items-center">
                <slot name="right">
                    <UserMenu v-if="auth.user" />
                    <Button v-else as-child size="sm" variant="outline">
                        <Link :href="login()">Log In</Link>
                    </Button>
                </slot>
            </div>
        </div>
    </header>
</template>
