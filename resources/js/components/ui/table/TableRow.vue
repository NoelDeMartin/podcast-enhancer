<script setup lang="ts">
import type { HTMLAttributes } from "vue"
import { cn } from "@/lib/utils"

const props = defineProps<{
  class?: HTMLAttributes["class"]
}>()
</script>

<template>
  <tr
    data-slot="table-row"
    :class="cn('hover:bg-neo-yellow/20 data-[state=selected]:bg-neo-yellow/30 border-b-3 border-neo-dark transition-colors', props.class)"
  >
    <slot />
  </tr>
</template>
