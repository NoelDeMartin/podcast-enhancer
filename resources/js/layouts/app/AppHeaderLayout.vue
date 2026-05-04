<script setup lang="ts">
import AppContent from '@/components/AppContent.vue';
import AppFooter from '@/components/AppFooter.vue';
import AppHeader from '@/components/AppHeader.vue';
import AppShell from '@/components/AppShell.vue';

defineProps<{
    floatingHeader?: boolean;
    logoUrl?: string | null;
}>();
</script>

<template>
    <AppShell variant="header">
        <div
            class="flex flex-1 flex-col bg-neo-dots text-neo-dark selection:bg-neo-pink selection:text-white"
        >
            <AppHeader :floating-header :logo-url="logoUrl">
                <template #default>
                    <slot name="header" />
                </template>
                <template #right>
                    <slot name="header-right" />
                </template>
            </AppHeader>
            <AppContent variant="header" class="flex-1">
                <slot />
            </AppContent>
            <AppFooter />
        </div>
    </AppShell>
</template>
