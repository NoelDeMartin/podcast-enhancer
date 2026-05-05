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
                class="border-neo-dark focus-within:ring-primary relative h-10 w-10 rounded-none border-3 p-0 focus-within:ring-2"
            >
                <Avatar class="h-full w-full rounded-none">
                    <AvatarImage v-if="auth.user.avatar" :src="auth.user.avatar" alt="" />
                    <AvatarFallback
                        class="bg-neo-bg rounded-none font-semibold text-black dark:text-white"
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
