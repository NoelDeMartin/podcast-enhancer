<template>
    <Dialog :open="visible" @update:open="$event || close()">
        <DialogContent v-bind="$attrs">
            <DialogHeader v-if="title || description">
                <DialogTitle v-if="title">{{ title }}</DialogTitle>
                <DialogDescription v-if="description">{{ description }}</DialogDescription>
            </DialogHeader>

            <slot />

            <ModalComponent v-if="child" :is="child" />
        </DialogContent>
    </Dialog>
</template>

<script setup lang="ts">
import {
    Dialog,
    DialogContent,
    DialogDescription,
    DialogHeader,
    DialogTitle,
} from '@/components/ui/dialog';
import { ModalComponent } from '@noeldemartin/vue-modals';
import { useModal } from './utils';

defineProps<{
    title?: string;
    description?: string;
}>();

const { child, visible, close } = useModal();
</script>
