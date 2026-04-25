<script setup lang="ts">
import type { HTMLAttributes } from "vue"
import { cn } from "@/lib/utils"

const props = defineProps<{
  class?: HTMLAttributes["class"]
}>()
</script>

<template>
  <div
    data-slot="card"
    :class="
      cn(
        'bg-card text-card-foreground flex flex-col gap-6 rounded-none border-3 border-neo-dark py-6 shadow-neo-hard-hard',
        props.class,
      )
    "
  >
    <slot />
  </div>
</template>
