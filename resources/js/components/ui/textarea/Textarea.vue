<script setup lang="ts">
import type { HTMLAttributes } from "vue"
import { useVModel } from "@vueuse/core"
import { cn } from "@/lib/utils"

const props = defineProps<{
  class?: HTMLAttributes["class"]
  defaultValue?: string | number
  modelValue?: string | number
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
  <textarea
    v-model="modelValue"
    data-slot="textarea"
    :class="cn('border-neo-dark placeholder:text-muted-foreground focus-visible:ring-primary focus-visible:ring-offset-1 aria-invalid:ring-destructive aria-invalid:border-destructive flex field-sizing-content min-h-16 w-full rounded-none border-3 bg-transparent px-3 py-2 text-base transition-[color,box-shadow-neo-hard] outline-none focus-visible:ring-2 disabled:cursor-not-allowed disabled:opacity-50 md:text-sm', props.class)"
  />
</template>
