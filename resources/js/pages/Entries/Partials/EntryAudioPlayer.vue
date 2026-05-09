<script setup lang="ts">
import { useMediaControls } from '@vueuse/core';
import { computed, onMounted, ref, useTemplateRef, watch } from 'vue';

import { formatEntryTimestamp } from '@/lib/entries';

const props = withDefaults(
    defineProps<{
        src: string;
        openHref?: string | null;
    }>(),
    {
        openHref: null,
    },
);

const audio = useTemplateRef<HTMLAudioElement>('audio');

const { playing, waiting, currentTime, duration, volume, muted, rate } = useMediaControls(audio, {
    src: props.src,
});
const isFloating = ref(false);

watch(playing, (isPlaying) => {
    if (isPlaying) {
        isFloating.value = true;
    }
});

onMounted(() => {
    if (Number.isNaN(volume.value)) {
        volume.value = 1;
    }
});

const safeDuration = computed(() => (Number.isFinite(duration.value) ? duration.value : 0));
const safeCurrentTime = computed(() =>
    Number.isFinite(currentTime.value) ? currentTime.value : 0,
);

const progressPercent = computed(() => {
    if (!safeDuration.value) {
        return 0;
    }

    return Math.min(100, Math.max(0, (safeCurrentTime.value / safeDuration.value) * 100));
});

const volumePercent = computed(() => Math.min(100, Math.max(0, (Number(volume.value) || 0) * 100)));

const formatClock = (seconds: number) => formatEntryTimestamp(seconds, safeDuration.value);

const toggleMute = () => {
    muted.value = !muted.value;
};

const clampTime = (value: number) => {
    if (!safeDuration.value) {
        return Math.max(0, value);
    }

    return Math.min(safeDuration.value, Math.max(0, value));
};

const seekBy = (deltaSeconds: number) => {
    currentTime.value = clampTime(safeCurrentTime.value + deltaSeconds);
};

const seekTo = (seconds: number) => {
    currentTime.value = clampTime(seconds);
    playing.value = true;
};

const closeFloating = () => {
    if (playing.value) {
        playing.value = false;
    }

    isFloating.value = false;
};

const cycleRate = () => {
    const options = [1, 1.25, 1.5, 2] as const;
    const current = Number(rate.value) || 1;
    const index = options.findIndex((v) => Math.abs(v - current) < 0.001);
    rate.value = options[(index + 1) % options.length];
};

defineExpose({
    seekTo,
});
</script>

<template>
    <div
        class="bg-neo-dark text-white"
        :class="{ 'fixed inset-x-0 bottom-0 z-50': isFloating }"
        :data-floating-player="isFloating ? 'true' : undefined"
    >
        <audio ref="audio" class="sr-only" preload="metadata" />

        <div
            class="grid grid-cols-[auto_1fr_auto_auto] items-center p-3 [grid-template-areas:'seek_seek_seek_close'_'play_controls_meta_meta'] sm:grid-cols-[auto_1fr_1fr_auto] sm:grid-rows-[1fr_auto] sm:[grid-template-areas:'play_seek_seek_close'_'play_controls_meta_close']"
        >
            <div class="min-w-0 pb-3 pl-1.5 [grid-area:seek] sm:py-1 sm:pl-3">
                <div class="relative h-2 w-full bg-white/15">
                    <div
                        class="bg-primary absolute inset-y-0 left-0"
                        :style="{ width: `${progressPercent}%` }"
                    />
                    <div
                        class="absolute top-1/2 size-3 -translate-y-1/2 bg-white"
                        :style="{ left: `calc(${progressPercent}% - 6px)` }"
                    />
                    <input
                        v-model.number="currentTime"
                        :max="safeDuration"
                        min="0"
                        step="0.1"
                        type="range"
                        class="absolute inset-0 h-full w-full cursor-pointer opacity-0"
                        :disabled="safeDuration === 0"
                        aria-label="Seek"
                    />
                </div>
            </div>

            <div class="flex justify-end pl-1 [grid-area:close]">
                <button
                    v-if="isFloating"
                    type="button"
                    class="inline-flex aspect-square size-12 shrink-0 items-center justify-center text-white/70 transition-colors hover:bg-white/10 hover:text-white active:translate-y-px"
                    @click="closeFloating"
                    aria-label="Close floating player"
                    title="Close floating player"
                >
                    <i-carbon-close class="size-6" />
                </button>
            </div>

            <div class="flex items-center [grid-area:play]">
                <button
                    type="button"
                    class="bg-primary text-primary-foreground hover:shadow-neo-hard inline-flex aspect-square size-12 items-center justify-center transition-all active:translate-y-px"
                    @click="playing = !playing"
                    :aria-label="playing ? 'Pause' : 'Play'"
                >
                    <i-svg-spinners-180-ring v-if="waiting" class="size-7" />
                    <i-carbon-pause-filled v-else-if="playing" class="size-7" />
                    <i-carbon-play-filled-alt v-else class="size-7 translate-x-px" />
                </button>
            </div>

            <div class="flex min-w-0 items-center gap-2 overflow-hidden [grid-area:controls]">
                <button
                    type="button"
                    class="inline-flex size-9 items-center justify-center text-white/85 transition-colors hover:text-white"
                    @click="cycleRate"
                    :aria-label="`Playback speed ${rate}x (click to change)`"
                >
                    <span class="text-xs font-black tabular-nums">{{ rate }}x</span>
                </button>

                <button
                    type="button"
                    class="inline-flex size-9 items-center justify-center text-white/85 transition-colors hover:text-white"
                    @click="seekBy(-10)"
                    aria-label="Back 10 seconds"
                >
                    <i-carbon-skip-back class="size-5" />
                </button>

                <button
                    type="button"
                    class="inline-flex size-9 items-center justify-center text-white/85 transition-colors hover:text-white"
                    @click="seekBy(30)"
                    aria-label="Forward 30 seconds"
                >
                    <i-carbon-skip-forward class="size-5" />
                </button>

                <button
                    type="button"
                    class="xs:flex hidden size-9 items-center justify-center text-white/85 transition-colors hover:text-white"
                    @click="currentTime = 0"
                    aria-label="Restart"
                >
                    <i-carbon-reset-alt class="size-5" />
                </button>

                <div class="hidden items-center gap-2 sm:flex">
                    <button
                        type="button"
                        class="inline-flex size-9 items-center justify-center text-white/85 transition-colors hover:text-white"
                        @click="toggleMute"
                        :aria-pressed="muted"
                        :aria-label="muted ? 'Unmute' : 'Mute'"
                    >
                        <i-carbon-volume-mute v-if="muted || volume === 0" class="size-5" />
                        <i-carbon-volume-up v-else class="size-5" />
                    </button>

                    <div class="relative h-2 w-20 bg-white/15">
                        <div
                            class="bg-primary absolute inset-y-0 left-0"
                            :style="{ width: `${muted ? 0 : volumePercent}%` }"
                        />
                        <div
                            class="absolute top-1/2 size-3 -translate-y-1/2 bg-white"
                            :style="{ left: `calc(${muted ? 0 : volumePercent}% - 6px)` }"
                        />
                        <input
                            v-model.number="volume"
                            min="0"
                            max="1"
                            step="0.01"
                            type="range"
                            class="absolute inset-0 size-full cursor-pointer opacity-0"
                            aria-label="Volume"
                        />
                    </div>
                </div>
            </div>

            <div class="flex items-center justify-end gap-3 [grid-area:meta]">
                <a
                    v-if="openHref"
                    :href="openHref"
                    download
                    class="inline-flex size-9 shrink-0 items-center justify-center text-white/85 transition-colors hover:text-white"
                    aria-label="Download audio file"
                    title="Download"
                >
                    <i-carbon-download class="size-5" />
                </a>
                <div
                    class="text-[11px] font-bold whitespace-nowrap text-white/80 tabular-nums sm:text-xs"
                >
                    {{ formatClock(safeCurrentTime) }} / {{ formatClock(safeDuration) }}
                </div>
            </div>
        </div>
    </div>
</template>
