<script lang="ts" setup>

const visible   = ref(false);
const color     = ref<string>('error');
const message   = ref<string>('');

const show = (_message: string, _color: string) => {
    message.value   = _message;
    color.value     = _color;
    visible.value   = true;
};

defineExpose({
    show
});
</script>

<template>
  <VSnackbar
    v-model="visible"
    location="top"
    variant="flat"
    transition="scroll-y-reverse-transition"
    :timeout="3000"
    :color="color"
  >
    {{ message }}
  </VSnackbar>
</template>
