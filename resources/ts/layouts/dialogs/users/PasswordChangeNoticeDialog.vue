<script lang="ts" setup>
import PasswordChangeDialog from '@/layouts/dialogs/users/PasswordChangeDialog.vue'
import { axios, getUserLevel, user_info } from '@axios'

const visible = ref(false)
const snackbar = <any>(inject('snackbar'))
const password = ref()

const show = () => {
    visible.value = true
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
    visible.value = false
}

const extendPassword = async () => {
    const res = await axios.post('/api/v1/auth/extend-password-at')
    snackbar.value.show('성공하였습니다.', 'success')
    user_info.value.password_change_at = res.data.password_change_at
    visible.value = false
}

defineExpose({
    show
});
</script>
<template>
    <VDialog v-model="visible" persistent class="v-dialog-sm">
        <!-- Dialog close btn -->
        <DialogCloseBtn @click="visible = !visible" />
        <!-- Dialog Content -->
        <VCard title="경고">
            <VCardText>
                <span>비밀번호를 변경한지 90일이 초과되었습니다.<br>비밀번호를 변경해주세요.</span>
                <br>
            </VCardText>
            <VCardText class="d-flex justify-end gap-3 flex-wrap">
                <VBtn color="secondary" variant="tonal" @click="extendPassword">
                    알림 1개월 연장하기
                </VBtn>
                <VBtn @click="changePassword">
                    비밀번호 변경
                </VBtn>
            </VCardText>
        </VCard>
    </VDialog>
    <PasswordChangeDialog ref="password"/>
</template>
