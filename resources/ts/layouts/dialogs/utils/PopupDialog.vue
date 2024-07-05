<script lang="ts" setup>
import type { Popup } from '@/views/types'
import { PopupEvent } from '@core/utils/popup'

const { setOpenStatus, init } = PopupEvent('popups/hide/')
const popups = ref<Popup[]>([])

const show = (_popups: Popup[]) => {
    popups.value = _popups
    popups.value.forEach(popup => {
        init(popup)
    });
}

defineExpose({
    show
});
</script>
<template>
    <VDialog v-model="popup.visible" persistent v-for="(popup, index) in popups" :key="index" max-width="900" style="height: 90% !important;">
        <!-- Dialog close btn -->
        <div class="button-container">
            <VCheckbox v-model="popup.is_hide" class="check-label not-open-today" label="오늘 안보기" />
            <DialogCloseBtn @click="setOpenStatus(popup)" />
        </div>
        <!-- Dialog Content -->
        <VCard :title=popup.popup_title>
            <VDivider style="margin-top: 1em;"/>
            <div v-html="popup.popup_content" style="padding: 1em;"></div>
        </VCard>
    </VDialog>
</template>
<style scoped>
:deep(.v-overlay__content) {
  inline-size: fit-content !important;
  min-inline-size: 350px !important;
}

:deep(img) {
  block-size: 100%;
  inline-size: 100%;
  object-fit: cover;
}

</style>
