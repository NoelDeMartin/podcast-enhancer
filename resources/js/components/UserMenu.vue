<script setup lang="ts">
import { usePage } from '@inertiajs/vue3';
import { computed } from 'vue';

import { Avatar, AvatarFallback, AvatarImage } from '@/components/ui/avatar';
import { Button } from '@/components/ui/button';
import {
    DropdownMenu,
    DropdownMenuContent,
    DropdownMenuTrigger,
} from '@/components/ui/dropdown-menu';
import UserMenuContent from '@/components/UserMenuContent.vue';
import { getInitials } from '@/composables/useInitials';

const page = usePage();
const auth = computed(() => page.props.auth);
</script>

<template>
    <DropdownMenu v-if="auth.user">
        <DropdownMenuTrigger as-child>
            <Button
                variant="ghost"
                size="icon"
                class="group relative size-10 rounded-none p-0 hover:bg-transparent"
            >
                <Avatar
                    class="bg-neo-dark group-hover:shadow-neo-hard size-full transition-all duration-300 group-active:translate-x-[2px] group-active:translate-y-[2px] group-active:shadow-none"
                >
                    <AvatarImage v-if="auth.user.avatar" :src="auth.user.avatar" alt="" />

                    <AvatarFallback class="bg-neo-bg font-semibold text-black dark:text-white">
                        {{ getInitials(auth.user?.name) }}
                    </AvatarFallback>
                </Avatar>
                <div
                    v-if="auth.user.plan === 'pro'"
                    class="bg-primary border-neo-dark absolute -bottom-2 left-1/2 z-10 -translate-x-1/2 border-2 px-1 text-[10px] font-black tracking-tighter text-white uppercase"
                >
                    Pro
                </div>
            </Button>
        </DropdownMenuTrigger>
        <DropdownMenuContent align="end" class="w-56">
            <UserMenuContent :user="auth.user" />
        </DropdownMenuContent>
    </DropdownMenu>
</template>
