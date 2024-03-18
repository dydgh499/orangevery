<script lang="ts" setup>
import type { Popup } from '@/views/types'

const not_open_key = 'popup-not-visibles'
const not_opens = ref(<number[]>([]))
const popups = ref<Popup[]>([])

const show = (_popups: Popup[]) => {
    popups.value = _popups
    popups.value.forEach(popup => {
        popup.visible = not_opens.value.includes(popup.id) ? false : true
    });
}

const setNotOpenToday = (id: number) => {
    not_opens.value.push(id)
}

var setCookie = function(name: string, value: string, exp: number) {
    var date = new Date();
    date.setTime(date.getTime() + exp*24*60*60*1000);
    document.cookie = name + '=' + value + ';expires=' + date.toUTCString() + ';path=/';
};

const getCookie = (name: string) => {
     var value = document.cookie.match('(^|;) ?' + name + '=([^;]*)(;|$)');
     return value? value[2] : null;  
}


onMounted(() => {
    not_opens.value = JSON.parse(getCookie(not_open_key) || "[]") 
    watchEffect(() => {    
        setCookie(not_open_key, JSON.stringify(not_opens.value), 1)
    })
})

defineExpose({
    show
});
</script>
<template>
    <VDialog v-model="popup.visible" persistent v-for="(popup, index) in popups" :key="index" max-width="900">
        <!-- Dialog close btn -->
        <div class="button-container">
            <VCheckbox  class="check-label not-open-today" label="오늘 안보기" @click="setNotOpenToday(popup.id)"/>
            <DialogCloseBtn @click="popup.visible = !popup.visible" />
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
  min-inline-size: 400px !important;
}

:deep(img) {
  block-size: 100%;
  inline-size: 100%;
  object-fit: cover;
}

.button-container {
  display: flex;
  justify-content: flex-end;
}
.not-open-today {
    position: absolute;
    z-index: 9999;
    right: 2em;
}
</style>
