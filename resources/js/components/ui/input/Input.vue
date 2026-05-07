<script setup lang="ts">
import type { HTMLAttributes } from "vue"
import { useVModel } from "@vueuse/core"
import { cn } from "@/lib/utils"

const props = defineProps<{
  defaultValue?: string | number
  modelValue?: string | number
  class?: HTMLAttributes["class"]
}>()

const emits = defineEmits<{
  (e: "update:modelValue", payload: string | number): void
}>()

const modelValue = useVModel(props, "modelValue", emits, {
  passive: true,
  defaultValue: props.defaultValue,
})
</script>

<template>
  <input
    v-model="modelValue"
    data-slot="input"
    :class="cn(
      'file:text-foreground placeholder:text-muted-foreground selection:bg-primary selection:text-white border-neo-dark h-10 w-full min-w-0 rounded-none border-3 bg-white px-3 py-1 text-base transition-[color,box-shadow-neo-hard] outline-none file:inline-flex file:h-7 file:border-0 file:bg-white file:text-sm file:font-medium disabled:pointer-events-none disabled:cursor-not-allowed disabled:opacity-50 md:text-sm',
      'focus-visible:ring-primary focus-visible:ring-2 focus-visible:ring-offset-1',
      'aria-invalid:ring-destructive aria-invalid:border-destructive',
      props.class,
    )"
  >
</template>
