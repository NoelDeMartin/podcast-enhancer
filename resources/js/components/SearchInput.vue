<script setup lang="ts">
import { Input } from '@/components/ui/input';

defineProps<{
    placeholder?: string;
}>();

const modelValue = defineModel<string>();
</script>

<template>
    <div class="relative w-full">
        <i-carbon-search class="text-muted-foreground absolute top-2.5 left-2.5 size-4" />
        <Input
            v-model="modelValue"
            type="search"
            :placeholder="placeholder || 'Search...'"
            class="pl-8"
        />
    </div>
</template>
