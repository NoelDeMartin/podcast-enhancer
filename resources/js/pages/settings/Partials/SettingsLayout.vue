<script setup lang="ts">
import { Link } from '@inertiajs/vue3';

import Heading from '@/components/Heading.vue';
import { Button } from '@/components/ui/button';
import { useCurrentUrl } from '@/composables/useCurrentUrl';
import { toUrl } from '@/lib/utils';
import { edit as editProfile } from '@/routes/profile';
import { edit as editSecurity } from '@/routes/security';
import type { NavItem } from '@/types';

const sidebarNavItems: NavItem[] = [
    {
        title: 'Profile',
        href: editProfile(),
    },
    {
        title: 'Security',
        href: editSecurity(),
    },
];

const { isCurrentOrParentUrl } = useCurrentUrl();
</script>

<template>
    <div class="mx-auto w-full max-w-7xl px-4 py-6 md:px-6">
        <Heading
            title="Settings"
            description="Manage your profile and account settings"
            class="mb-4 md:mb-8"
        />

        <div class="flex flex-col md:flex-row md:space-x-12">
            <aside
                class="mb-4 w-full bg-gray-200 p-2 md:mr-4 md:w-48 md:max-w-xl md:bg-transparent md:p-0"
            >
                <nav class="flex flex-col space-y-1 space-x-0" aria-label="Settings">
                    <Button
                        v-for="item in sidebarNavItems"
                        :key="toUrl(item.href)"
                        variant="ghost"
                        :class="[
                            'w-full justify-start',
                            { 'border-2': isCurrentOrParentUrl(item.href) },
                        ]"
                        as-child
                    >
                        <Link :href="item.href">
                            <component :is="item.icon" class="size-4" />
                            {{ item.title }}
                        </Link>
                    </Button>
                </nav>
            </aside>

            <section class="space-y-12 md:max-w-xl">
                <slot />
            </section>
        </div>
    </div>
</template>
