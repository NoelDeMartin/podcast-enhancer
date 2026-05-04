<script setup lang="ts">
import AppHeaderLayout from '@/layouts/app/AppHeaderLayout.vue';

type Props = {
    floatingHeader?: boolean;
    logoUrl?: string | null;
};

defineProps<Props>();
</script>

<template>
    <AppHeaderLayout :floating-header :logo-url="logoUrl">
        <template #header>
            <slot name="header" />
        </template>
        <template #header-right>
            <slot name="header-right" />
        </template>
        <slot />
    </AppHeaderLayout>
</template>
