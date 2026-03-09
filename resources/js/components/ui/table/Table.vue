<script setup lang="ts">
import  { HTMLAttributes } from "vue"
import { cn } from "@/lib/utils"

const props = defineProps<{
  class?: HTMLAttributes["class"]
}>()

</script>

<template>
  <div class="scrollable relative w-full flex-1 overflow-auto max-h-[calc(100vh-150px)] sm:pb-3">
    <table :class="cn('w-full caption-bottom text-sm', props.class)">
      <slot />
    </table>
  </div>
</template>

<style>
.scrollable::-webkit-scrollbar {
    width: 4px;
    height: 4px;
}

.scrollable::-webkit-scrollbar-track {
    background: transparent;
}

.scrollable::-webkit-scrollbar-thumb {
    background-color: rgba(100,100,100,0.3);
}
</style>
