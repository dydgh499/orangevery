<script setup lang="ts">
interface Props {
  icon?: string
}

const props = withDefaults(defineProps<Props>(), {
  icon: 'tabler-x',
})
</script>

<template>
  <VBtn
    icon
    class="v-dialog-close-btn"
  >
    <VIcon :icon="props.icon" />
  </VBtn>
</template>
<style scoped>
.v-dialog-close-btn {
  position: absolute;
  z-index: 1;
  border-radius: 0.375rem;
  background-color: rgb(var(--v-theme-surface)) !important;
  block-size: 32px !important;
  color: rgba(var(--v-theme-on-surface), var(--v-disabled-opacity)) !important;
  inline-size: 32px !important;
  inset-block-start: 0;
  inset-inline-end: 0;
  transform: translate(0.5rem, -0.5rem);
}
</style>
