<script setup lang="ts">
import { usePage } from '@inertiajs/vue3';
import { showModal } from '@noeldemartin/vue-modals';
import { computed } from 'vue';

import CreditsModal from '@/components/CreditsModal.vue';
import { Button } from '@/components/ui/button';
import { formatCredits } from '@/lib/credits';

const page = usePage();
const user = computed(() => page.props.auth.user);

function openCreditsModal() {
    showModal(CreditsModal);
}
</script>

<template>
    <Button v-if="user" variant="outline" @click="openCreditsModal">
        <i-tabler-coins class="size-4" />
        <span class="sr-only">Credits:</span>
        <span>{{ formatCredits(user.credits) }}</span>
    </Button>
</template>
