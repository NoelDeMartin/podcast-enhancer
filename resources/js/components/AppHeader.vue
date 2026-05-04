<script setup lang="ts">
import { Link, usePage } from '@inertiajs/vue3';
import Menu from '~icons/lucide/menu';
import { computed, onBeforeUnmount, onMounted, ref } from 'vue';
import AppLogo from '@/components/AppLogo.vue';
import { Avatar, AvatarFallback, AvatarImage } from '@/components/ui/avatar';
import { Button } from '@/components/ui/button';
import {
    DropdownMenu,
    DropdownMenuContent,
    DropdownMenuTrigger,
} from '@/components/ui/dropdown-menu';
import {
    Sheet,
    SheetContent,
    SheetDescription,
    SheetHeader,
    SheetTitle,
    SheetTrigger,
} from '@/components/ui/sheet';
import UserMenuContent from '@/components/UserMenuContent.vue';
import { getInitials } from '@/composables/useInitials';
import { dashboard, login } from '@/routes';

const props = defineProps<{
    floatingHeader?: boolean;
    logoUrl?: string | null;
}>();

const page = usePage();
const auth = computed(() => page.props.auth);

const logoHref = computed(() => {
    if (props.logoUrl === null) {
        return null;
    }

    return props.logoUrl ?? dashboard();
});

const showMarketingBar = ref(false);
let rafId: number | null = null;

function updateMarketingBarVisibility() {
    const threshold = window.innerHeight * 0.3;
    showMarketingBar.value = window.scrollY > threshold;
}

function onScrollOrResize() {
    if (rafId !== null) {
        return;
    }

    rafId = window.requestAnimationFrame(() => {
        rafId = null;
        updateMarketingBarVisibility();
    });
}

onMounted(() => {
    if (!props.floatingHeader) {
        return;
    }

    updateMarketingBarVisibility();
    window.addEventListener('scroll', onScrollOrResize, { passive: true });
    window.addEventListener('resize', onScrollOrResize, { passive: true });
});

onBeforeUnmount(() => {
    if (!props.floatingHeader) {
        return;
    }

    window.removeEventListener('scroll', onScrollOrResize);
    window.removeEventListener('resize', onScrollOrResize);
    if (rafId !== null) {
        window.cancelAnimationFrame(rafId);
    }
});
</script>

<template>
    <nav
        v-if="floatingHeader"
        class="fixed top-0 right-0 left-0 z-50 border-b-3 border-neo-dark bg-neo-bg transition-all duration-400 ease-out"
        :class="
            showMarketingBar
                ? 'translate-y-0 pointer-events-auto'
                : '-translate-y-full pointer-events-none'
        "
    >
        <div class="mx-auto flex h-16 max-w-7xl items-center justify-between px-4 sm:px-6">
            <Link
                v-if="logoHref !== null"
                :href="logoHref"
                v-scroll-on-click
                class="text-lg font-black uppercase tracking-tighter sm:text-2xl"
            >
                Podcast <span class="text-neo-pink">Enhancer</span>
            </Link>
            <span v-else class="text-lg font-black uppercase tracking-tighter sm:text-2xl">
                Podcast <span class="text-neo-pink">Enhancer</span>
            </span>

            <slot />

            <div class="flex items-center gap-4">
                <slot name="right">
                    <template v-if="auth.user">
                        <DropdownMenu>
                            <DropdownMenuTrigger as-child>
                                <Button
                                    variant="ghost"
                                    size="icon"
                                    class="relative h-10 w-10 rounded-none border-2 border-neo-dark p-0 focus-within:ring-2 focus-within:ring-primary"
                                >
                                    <Avatar class="h-full w-full rounded-none">
                                        <AvatarImage
                                            v-if="auth.user.avatar"
                                            :src="auth.user.avatar"
                                            alt=""
                                        />
                                        <AvatarFallback
                                            class="rounded-none bg-neo-bg font-semibold text-black dark:text-white"
                                        >
                                            {{ getInitials(auth.user?.name) }}
                                        </AvatarFallback>
                                    </Avatar>
                                </Button>
                            </DropdownMenuTrigger>
                            <DropdownMenuContent align="end" class="w-56">
                                <UserMenuContent :user="auth.user" />
                            </DropdownMenuContent>
                        </DropdownMenu>
                    </template>
                    <Button v-else as-child size="sm">
                        <Link :href="login()">Log In</Link>
                    </Button>
                </slot>
            </div>
        </div>
    </nav>

    <header v-else class="border-b-3 border-neo-dark bg-neo-bg">
        <div class="mx-auto flex h-16 max-w-7xl items-center justify-between px-4 sm:px-6">
            <div class="flex items-center gap-4">
                <div class="lg:hidden">
                    <Sheet>
                        <SheetTrigger as-child>
                            <Button variant="ghost" size="icon" class="h-10 w-10">
                                <Menu class="h-6 w-6" />
                            </Button>
                        </SheetTrigger>
                        <SheetContent side="left" class="w-[300px] p-6">
                            <SheetHeader class="text-left">
                                <SheetTitle class="sr-only">Navigation menu</SheetTitle>
                                <SheetDescription class="sr-only">
                                    Access the main navigation links.
                                </SheetDescription>
                                <Link
                                    v-if="logoHref !== null"
                                    :href="logoHref"
                                    class="flex items-center"
                                >
                                    <AppLogo />
                                </Link>
                                <span v-else class="flex items-center">
                                    <AppLogo />
                                </span>
                            </SheetHeader>
                        </SheetContent>
                    </Sheet>
                </div>

                <Link v-if="logoHref !== null" :href="logoHref" class="flex items-center">
                    <AppLogo />
                </Link>
                <span v-else class="flex items-center">
                    <AppLogo />
                </span>
            </div>

            <div class="flex items-center space-x-2">
                <template v-if="auth.user">
                    <DropdownMenu>
                        <DropdownMenuTrigger as-child>
                            <Button
                                variant="ghost"
                                size="icon"
                                class="relative h-10 w-10 rounded-none border-2 border-neo-dark p-0 focus-within:ring-2 focus-within:ring-primary"
                            >
                                <Avatar class="h-full w-full rounded-none">
                                    <AvatarImage
                                        v-if="auth.user.avatar"
                                        :src="auth.user.avatar"
                                        alt=""
                                    />
                                    <AvatarFallback
                                        class="rounded-none bg-neo-bg font-semibold text-black dark:text-white"
                                    >
                                        {{ getInitials(auth.user?.name) }}
                                    </AvatarFallback>
                                </Avatar>
                            </Button>
                        </DropdownMenuTrigger>
                        <DropdownMenuContent align="end" class="w-56">
                            <UserMenuContent :user="auth.user" />
                        </DropdownMenuContent>
                    </DropdownMenu>
                </template>
                <template v-else>
                    <Button as-child variant="ghost" size="sm">
                        <Link :href="login()">Log In</Link>
                    </Button>
                </template>
            </div>
        </div>
    </header>
</template>
