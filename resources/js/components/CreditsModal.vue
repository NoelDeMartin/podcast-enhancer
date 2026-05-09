<script setup lang="ts">
import { Link, usePage } from '@inertiajs/vue3';
import { useIntervalFn } from '@vueuse/core';
import { computed, onMounted, ref, watch } from 'vue';

import { show } from '@/actions/App/Http/Controllers/EntryController';
import { Button } from '@/components/ui/button';
import { Modal } from '@/components/ui/modal';
import { useModal } from '@/components/ui/modal/utils';
import {
    Table,
    TableBody,
    TableCell,
    TableHead,
    TableHeader,
    TableRow,
} from '@/components/ui/table';
import { useFailureModal } from '@/composables/useFailureModal';
import { formatCredits } from '@/lib/credits';
import { formatDateTime, getBatchStatus } from '@/lib/entries';
import { creditsUsage } from '@/routes';
import type { CreditUsage } from '@/types';

const { close } = useModal();
const { viewFailure } = useFailureModal();
const page = usePage();
const user = computed(() => page.props.auth.user);

const usages = ref<CreditUsage[]>([]);
const loading = ref(true);

const hasActiveJobs = computed(() =>
    usages.value.some((usage) => usage.entry && getBatchStatus(usage.entry) === 'pending'),
);

async function fetchUsages() {
    try {
        const response = await fetch(creditsUsage().url, {
            headers: {
                Accept: 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
            },
        });

        if (response.ok) {
            const data = await response.json();
            usages.value = data.usages;
            page.props.auth.user.credits = data.current_credits;
        }
    } finally {
        loading.value = false;
    }
}

const { pause, resume } = useIntervalFn(fetchUsages, 3000, { immediate: false });

watch(hasActiveJobs, (active) => (active ? resume() : pause()), { immediate: true });

onMounted(fetchUsages);
</script>

<template>
    <Modal
        title="Credits"
        description="Credits are used to process audio and generate enhancements, one credit is consumed for each minute."
        class="sm:max-w-2xl"
    >
        <div class="space-y-6">
            <div
                v-if="typeof user.credits === 'number'"
                class="bg-neo-dark flex flex-col gap-4 p-6 text-white sm:flex-row sm:items-start sm:justify-between"
            >
                <div class="space-y-3">
                    <h3 class="text-lg leading-none font-bold">Your current balance</h3>
                    <div class="flex items-center gap-3">
                        <i-tabler-coins class="size-8 shrink-0" />
                        <span class="text-4xl font-black tabular-nums">{{
                            formatCredits(user.credits)
                        }}</span>
                    </div>
                </div>
                <a
                    class="text-neo-dark hover:bg-primary focus-visible:ring-primary self-start bg-white px-4 py-2 font-mono font-bold uppercase ring-offset-black focus-visible:ring-2 focus-visible:ring-offset-2 focus-visible:outline-none sm:self-center"
                    href="mailto:hey@noeldemartin.com?subject=Podcast+Enhancer"
                >
                    Get more credits
                </a>
            </div>

            <div class="mt-4 space-y-4">
                <h3 class="px-1 text-lg font-bold">Usage History</h3>
                <div class="border-neo-dark max-h-96 overflow-y-auto border-3">
                    <Table class="table-fixed border-collapse">
                        <TableHeader>
                            <TableRow>
                                <TableHead class="w-[130px] sm:w-[180px]">Date</TableHead>
                                <TableHead>Description</TableHead>
                                <TableHead class="w-[70px] text-right sm:w-[80px]"
                                    >Credits</TableHead
                                >
                            </TableRow>
                        </TableHeader>
                        <TableBody>
                            <TableRow v-if="loading">
                                <TableCell colspan="3" class="h-24 text-center font-bold"
                                    >Loading history...</TableCell
                                >
                            </TableRow>
                            <TableRow v-else-if="usages.length === 0">
                                <TableCell colspan="3" class="h-24 text-center font-bold"
                                    >You haven't used any credits yet.</TableCell
                                >
                            </TableRow>
                            <TableRow v-for="usage in usages" :key="usage.id">
                                <TableCell class="text-[10px] leading-tight font-bold sm:text-sm">
                                    {{ formatDateTime(usage.created_at) }}
                                </TableCell>
                                <TableCell>
                                    <div class="flex items-center gap-1">
                                        <i-carbon-renew
                                            v-if="
                                                usage.entry &&
                                                getBatchStatus(usage.entry) === 'pending'
                                            "
                                            class="size-4 shrink-0 animate-spin"
                                            aria-label="Processing"
                                        />
                                        <Button
                                            v-else-if="
                                                usage.entry &&
                                                getBatchStatus(usage.entry) === 'failed'
                                            "
                                            type="button"
                                            variant="ghost"
                                            size="icon-sm"
                                            class="text-destructive hover:bg-destructive/10 hover:text-destructive focus-visible:ring-destructive -ml-1.5"
                                            title="Processing failed"
                                            @click="viewFailure(usage.entry)"
                                        >
                                            <i-carbon-warning-alt-filled class="size-4" />
                                            <span class="sr-only">Processing failed</span>
                                        </Button>
                                        <Link
                                            v-if="usage.entry && usage.entry.feed"
                                            :href="
                                                show.url({
                                                    feed: usage.entry.feed.slug,
                                                    entry: usage.entry.slug,
                                                })
                                            "
                                            class="block truncate font-bold hover:underline"
                                            :title="usage.entry.name"
                                            @click="() => close()"
                                        >
                                            {{ usage.entry.name }}
                                        </Link>
                                        <div
                                            v-else-if="usage.entry"
                                            class="truncate font-bold"
                                            :title="usage.entry.name"
                                        >
                                            {{ usage.entry.name }}
                                        </div>
                                        <span v-else class="text-muted-foreground italic"
                                            >Unknown entry</span
                                        >
                                    </div>
                                </TableCell>

                                <TableCell class="text-right font-black text-red-600 tabular-nums">
                                    -{{ usage.credits }}
                                </TableCell>
                            </TableRow>
                        </TableBody>
                    </Table>
                </div>
            </div>
        </div>
    </Modal>
</template>
