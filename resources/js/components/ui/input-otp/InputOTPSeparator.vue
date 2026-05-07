<script setup lang="ts">
import type { HTMLAttributes } from "vue"
import Subtract from "~icons/carbon/subtract"
import { useForwardProps } from "reka-ui"

const props = defineProps<{ class?: HTMLAttributes["class"] }>()

const forwarded = useForwardProps(props)
</script>

<template>
  <div
    data-slot="input-otp-separator"
    role="separator"
    v-bind="forwarded"
  >
    <slot>
      <Subtract />
    </slot>
  </div>
</template>
