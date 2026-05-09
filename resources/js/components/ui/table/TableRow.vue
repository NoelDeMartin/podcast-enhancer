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
    :class="cn('data-[state=selected]:bg-neo-yellow/30 border-b-3 border-neo-dark', props.class)"
  >
    <slot />
  </tr>
</template>
