<script lang="ts" setup>
import type { Popup } from '@/views/types'

const popups = ref<Popup[]>([])

const show = (_popups: Popup[]) => {
    popups.value = _popups
    popups.value.forEach(popup => {
        popup.visible = true
    });
}

defineExpose({
    show
});
</script>
<template>
    <VDialog v-model="popup.visible" persistent v-for="(popup, index) in popups" :key="index">
        <!-- Dialog close btn -->
        <DialogCloseBtn @click="popup.visible = !popup.visible" />
        <!-- Dialog Content -->
        <VCard :title=popup.popup_title>
            <VDivider style="margin-top: 1em;"/>
            <div v-html="popup.popup_content"></div>
        </VCard>
    </VDialog>
</template>
<style scoped>
:deep(.v-overlay__content) {
  inline-size: fit-content !important;
  min-inline-size: 400px !important;
}

:deep(img) {
  block-size: 100%;
  inline-size: 100%;
  object-fit: cover;
}
</style>
