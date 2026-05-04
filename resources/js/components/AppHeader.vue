<script setup lang="ts">
import { Link, usePage } from '@inertiajs/vue3';
import Menu from '~icons/lucide/menu';
import { computed } from 'vue';
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

const page = usePage();
const auth = computed(() => page.props.auth);
</script>

<template>
    <header class="border-b-3 border-neo-dark bg-neo-bg">
        <div class="mx-auto flex h-16 max-w-7xl items-center justify-between px-4">
            <div class="flex items-center gap-4">
                <!-- Mobile Menu -->
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
                                <Link :href="dashboard()" class="flex items-center">
                                    <AppLogo />
                                </Link>
                            </SheetHeader>
                        </SheetContent>
                    </Sheet>
                </div>

                <Link :href="dashboard()" class="flex items-center">
                    <AppLogo />
                </Link>
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
