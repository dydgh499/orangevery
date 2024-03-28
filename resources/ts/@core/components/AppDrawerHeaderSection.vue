<script setup lang="ts">
interface Props {
  title: string
}
interface Emit {
  (e: 'cancel', el: MouseEvent): void
}
const props = defineProps<Props>()

defineEmits<Emit>()
</script>

<template>
  <div class="pa-4 d-flex align-center">
    <h3> {{ props.title }} </h3>
    <VSpacer />

    <slot name="beforeClose" />

    <IconBtn
      variant="tonal"
      class="rounded"
      size="32"
      @click="$emit('cancel')"
    >
      <VIcon
        size="18"
        icon="tabler-x"
      />
    </IconBtn>
  </div>
</template>
