<script setup lang="ts">
import type { HTMLAttributes } from "vue"
import Renew from "~icons/carbon/renew"
import { cn } from "@/lib/utils"

const props = defineProps<{
  class?: HTMLAttributes["class"]
}>()
</script>

<template>
  <Renew
    role="status"
    aria-label="Loading"
    :class="cn('size-4 animate-spin', props.class)"
  />
</template>
