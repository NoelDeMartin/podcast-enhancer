<script setup lang="ts">
import AppContent from '@/components/AppContent.vue';
import AppFooter from '@/components/AppFooter.vue';
import AppHeader from '@/components/AppHeader.vue';
import { ModalsPortal } from '@/components/ui/modal';

type Props = {
    floatingHeader?: boolean;
    logoUrl?: string | null;
};

defineProps<Props>();
</script>

<template>
    <div class="flex min-h-screen w-full flex-col">
        <div class="flex flex-1 flex-col">
            <AppHeader :floating="floatingHeader" :logo-url="logoUrl">
                <template #default>
                    <slot name="header" />
                </template>
                <template #right>
                    <slot name="header-right" />
                </template>
            </AppHeader>
            <AppContent class="flex-1">
                <slot />
            </AppContent>
            <AppFooter />
            <ModalsPortal />
        </div>
    </div>
</template>
