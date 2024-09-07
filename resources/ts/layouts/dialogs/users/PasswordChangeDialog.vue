<script setup lang="ts">
import { getUserPasswordValidate, getUserTypeName } from '@/views/users/useStore'
import { axios, getUserLevel, user_info } from '@axios'
import { requiredValidatorV2 } from '@validators'


const alert = <any>(inject('alert'))
const snackbar = <any>(inject('snackbar'))
const errorHandler = <any>(inject('$errorHandler'))
const visible = ref(false)

const vForm = ref()
const user_type = ref(0)
const user_id = ref(0)
const is_my_password = ref(false)

const current_password = ref({
    is_show: false,
    value: '',
})
const new_password = ref({
    is_show: false,
    value: '',
})

const show = (_user_id: number, _user_type: number) => {
    if( _user_type !== 3) {
        user_id.value = _user_id
        user_type.value = _user_type
        visible.value = true

        if(user_id.value === user_info.value.id) {
            if(user_type.value === 0 && getUserLevel() === 10)
                is_my_password.value = true
            else if(user_type.value === 1 && getUserLevel() > 10 && getUserLevel() < 35)
                is_my_password.value = true
            else if(user_type.value === 2 && getUserLevel() >= 35)
                is_my_password.value = true
        }
    }
    else {
        snackbar.value.show(`잘못된 타입.`, 'warning')
    }
}


const submit = async () => {
    const [name, path] = getUserTypeName(user_type.value)
    const is_valid = await vForm.value.validate();
    if (is_valid.valid && await alert.value.show(`정말 패스워드를 변경하시겠습니까?`)) {
        try {
            const r = await axios.post(`/api/v1/manager/${path}/${user_id.value}/password-change`, {
                 user_pw: new_password.value.value,
                 current_pw: current_password.value.value
            })
            snackbar.value.show('성공하였습니다.', 'success')
            visible.value = false
        }
        catch (e: any) {
            snackbar.value.show(e.response.data.message, 'error')
            const r = errorHandler(e)
        }
    }
}

const handleEvent = (event: KeyboardEvent) => {
    event.preventDefault()
    submit()
}


const passwordRules = computed(() => {
    return getUserPasswordValidate(user_type.value, new_password.value.value)
})


defineExpose({
    show
});
</script>
<template>
    <VDialog v-model="visible" max-width="600">
        <!-- Dialog close btn -->
        <DialogCloseBtn @click="visible = false" />
        <!-- Dialog Content -->
        <VCard title="패스워드 변경">
            <VCardText>
                <VForm ref="vForm">
                    <VCol cols="12">
                        <template v-if="is_my_password">
                            <VRow no-gutters>
                                <div style=" width: 100%;">
                                    <h4>정보를 안전하게 보호하기 위해<span class="text-error"> 현재 비밀번호를 다시한번 확인합니다.</span></h4>
                                    <br>
                                    <h5>비밀번호는 항상 타인에게 노출되지 않도록 주의하시기 바랍니다.</h5>
                                </div>
                            </VRow>
                            <VRow no-gutters style="margin-top: 1em;">
                                <VCol :md="4" :cols="12" style="margin-top: 0.5em;">
                                    <label>현재 패스워드 입력</label>
                                </VCol>
                                <VCol :md="8" :cols="12">
                                    <VTextField v-model="current_password.value" counter prepend-inner-icon="tabler-lock"
                                        :rules="[requiredValidatorV2(current_password.value, '현재 패스워드')]"
                                        :append-inner-icon="current_password.is_show ? 'tabler-eye' : 'tabler-eye-off'"
                                        :type="current_password.is_show ? 'text' : 'password'" persistent-placeholder
                                        @click:append-inner="current_password.is_show = !current_password.is_show" 
                                    />
                                </VCol>
                            </VRow>
                        </template>
                        <VRow no-gutters style="margin-top: 1em;">
                            <VCol :md="4" :cols="12" style="margin-top: 0.5em;">
                                <label>새 패스워드 입력</label>
                            </VCol>
                            <VCol :md="8" :cols="12">
                                <VTextField v-model="new_password.value" counter prepend-inner-icon="tabler-lock"
                                    :rules="passwordRules"
                                    :append-inner-icon="new_password.is_show ? 'tabler-eye' : 'tabler-eye-off'"
                                    :type="new_password.is_show ? 'text' : 'password'" persistent-placeholder
                                    @click:append-inner="new_password.is_show = !new_password.is_show" 
                                    @keydown.enter="handleEvent"
                                />
                            </VCol>
                        </VRow>
                    </VCol>
                </VForm>
            </VCardText>
            <VCardText class="d-flex justify-end gap-3 flex-wrap">
                <VBtn color="secondary" variant="tonal" @click="visible = false">
                    취소
                </VBtn>
                <VBtn @click="submit()">
                    변경
                </VBtn>
            </VCardText>
        </VCard>
    </VDialog>
</template>
