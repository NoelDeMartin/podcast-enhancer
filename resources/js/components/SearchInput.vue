<script setup lang="ts">
import Search from '~icons/lucide/search';
import { Input } from '@/components/ui/input';

defineProps<{
    placeholder?: string;
}>();

const modelValue = defineModel<string>();
</script>

<template>
    <div class="relative w-full max-w-sm">
        <Search class="absolute left-2.5 top-2.5 h-4 w-4 text-muted-foreground" />
        <Input
            v-model="modelValue"
            type="search"
            :placeholder="placeholder || 'Search...'"
            class="pl-8"
        />
    </div>
</template>
