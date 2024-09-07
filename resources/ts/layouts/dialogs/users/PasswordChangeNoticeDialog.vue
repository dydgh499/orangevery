<script lang="ts" setup>
import PasswordChangeDialog from '@/layouts/dialogs/users/PasswordChangeDialog.vue';
import type { Popup } from '@/views/types';
import { getUserLevel, user_info } from '@axios';
import { PopupEvent } from '@core/utils/popup';

const password = ref()
const { setOpenStatus, init } = PopupEvent('password-change-notice/hide/')
const popup = ref(<Popup>({
    id: 0,
    visible: false,
    is_hide: false,
}))

const show = () => {
    init(popup.value)
}

const changePassword = () => {
    let type = null
    if( getUserLevel() === 10)
        type = 0
    else if(getUserLevel() <= 30)
        type = 1
    else if(getUserLevel() >= 35)
        type = 2

    password.value.show(user_info.value.id, type)
    popup.value.visible = false
}

const extendPassword = async () => {
    popup.value.is_hide = true
    setOpenStatus(popup.value, 30)    
}

defineExpose({
    show
});
</script>
<template>
    <VDialog v-model="popup.visible" persistent class="v-dialog-sm">
        <DialogCloseBtn @click="setOpenStatus(popup)" />
        <VCard title="경고">
            <VCardText>
                <span>비밀번호를 변경한지 90일이 초과되었습니다.<br>비밀번호를 변경해주세요.</span>
                <br>
            </VCardText>
            <VCardText class="d-flex justify-end gap-3 flex-wrap">
                <VBtn color="secondary" variant="tonal" @click="extendPassword">
                    30일 안보기
                </VBtn>
                <VBtn @click="changePassword">
                    비밀번호 변경
                </VBtn>
            </VCardText>
        </VCard>
    </VDialog>
    <PasswordChangeDialog ref="password"/>
</template>
