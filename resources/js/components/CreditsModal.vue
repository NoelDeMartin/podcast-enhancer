<script setup lang="ts">
import { Link, usePage } from '@inertiajs/vue3';
import { useIntervalFn } from '@vueuse/core';
import { computed, onMounted, ref, watch } from 'vue';

import { index } from '@/actions/App/Http/Controllers/CreditController';
import { show } from '@/actions/App/Http/Controllers/EntryController';
import ClientPagination from '@/components/ClientPagination.vue';
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
import { getBatchStatus } from '@/lib/entries';
import { formatDate } from '@/lib/utils';
import type { CreditUsage } from '@/types';

const { close } = useModal();
const { viewFailure } = useFailureModal();
const page = usePage();
const user = computed(() => page.props.auth.user);

const usages = ref<CreditUsage[]>([]);
const links = ref<any[]>([]);
const loading = ref(true);
const fetching = ref(false);
const currentUrl = ref<string | null>(null);

const hasActiveJobs = computed(() =>
    usages.value.some((usage) => usage.entry && getBatchStatus(usage.entry) === 'pending'),
);

async function fetchUsages(url: string | null = null, fromInterval = false) {
    if (url === null && currentUrl.value === null) {
        loading.value = true;
    }

    if (!fromInterval) {
        fetching.value = true;
    }

    try {
        const fetchUrl = new URL(url || currentUrl.value || index().url, page.props.appUrl);

        const response = await fetch(fetchUrl.toString(), {
            headers: {
                Accept: 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
            },
        });

        if (response.ok) {
            const data = await response.json();
            usages.value = data.usages.data;
            links.value = data.usages.links;
            currentUrl.value =
                data.usages.path +
                (data.usages.current_page > 1 ? `?page=${data.usages.current_page}` : '');
            page.props.auth.user.credits = data.current_credits;
        }
    } finally {
        loading.value = false;
        fetching.value = false;
    }
}

function handlePageChange(url: string | null) {
    if (url) {
        fetchUsages(url);
    }
}

const { pause, resume } = useIntervalFn(() => fetchUsages(null, true), 3000, { immediate: false });

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
                <h3 class="flex items-center gap-2 px-1 text-lg font-bold">
                    <span>Usage History</span>
                    <i-svg-spinners-180-ring v-if="fetching && !loading" class="size-4" />
                </h3>
                <div
                    class="border-neo-dark overflow-y-auto border-3 transition-opacity sm:max-h-96"
                    :class="{ 'opacity-50': fetching && !loading }"
                >
                    <Table class="table-fixed border-collapse">
                        <colgroup>
                            <col class="w-20 sm:w-44" />
                            <col />
                            <col class="w-12 sm:w-32" />
                        </colgroup>
                        <TableHeader>
                            <TableRow>
                                <TableHead class="whitespace-normal sm:whitespace-nowrap"
                                    >Date</TableHead
                                >
                                <TableHead class="min-w-0 whitespace-normal sm:whitespace-nowrap"
                                    >Episode</TableHead
                                >
                                <TableHead class="sr-only">Usage</TableHead>
                            </TableRow>
                        </TableHeader>
                        <TableBody>
                            <TableRow v-if="loading">
                                <TableCell colspan="3" class="h-24 text-center font-bold">
                                    <div class="flex flex-col items-center justify-center gap-2">
                                        <i-svg-spinners-180-ring class="size-8" />
                                        <span class="sr-only">Loading history...</span>
                                    </div>
                                </TableCell>
                            </TableRow>
                            <TableRow v-else-if="usages.length === 0">
                                <TableCell colspan="3" class="h-24 text-center font-bold"
                                    >You haven't used any credits yet.</TableCell
                                >
                            </TableRow>
                            <TableRow v-for="usage in usages" :key="`${usage.type}-${usage.id}`">
                                <TableCell
                                    class="align-top text-xs leading-snug font-bold whitespace-normal sm:align-middle sm:text-sm sm:whitespace-nowrap"
                                >
                                    <span class="hidden sm:inline">{{
                                        formatDate(usage.created_at, 'datetime')
                                    }}</span>
                                    <span class="inline sm:hidden">{{
                                        formatDate(usage.created_at, 'day-short')
                                    }}</span>
                                </TableCell>
                                <TableCell class="w-full max-w-0 min-w-0">
                                    <div
                                        v-if="usage.type === 'usage'"
                                        class="flex min-w-0 items-center gap-1"
                                    >
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
                                            class="min-w-0 flex-1 truncate font-bold hover:underline"
                                            :title="usage.entry.name"
                                            @click="() => close()"
                                        >
                                            {{ usage.entry.name }}
                                        </Link>
                                        <div
                                            v-else-if="usage.entry"
                                            class="min-w-0 flex-1 truncate font-bold"
                                            :title="usage.entry.name"
                                        >
                                            {{ usage.entry.name }}
                                        </div>
                                        <span
                                            v-else
                                            class="text-muted-foreground min-w-0 flex-1 truncate italic"
                                            >Unknown entry</span
                                        >
                                    </div>
                                    <div
                                        v-else
                                        class="truncate font-bold"
                                        :title="usage.description"
                                    >
                                        {{ usage.description }}
                                    </div>
                                </TableCell>

                                <TableCell
                                    class="text-right text-xs font-black whitespace-nowrap tabular-nums sm:text-sm"
                                    :class="
                                        usage.type === 'usage' ? 'text-red-600' : 'text-green-600'
                                    "
                                >
                                    {{ usage.type === 'usage' ? '-' : '+' }}{{ usage.credits }}
                                </TableCell>
                            </TableRow>
                        </TableBody>
                    </Table>
                </div>

                <ClientPagination
                    v-if="links.length > 3"
                    class="pt-2 transition-opacity"
                    :class="{ 'pointer-events-none opacity-50': fetching && !loading }"
                    label="Credits pagination"
                    :links="links"
                    :on-page-change="handlePageChange"
                />
            </div>
        </div>
    </Modal>
</template>
