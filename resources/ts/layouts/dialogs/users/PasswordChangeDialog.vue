<script setup lang="ts">
import CreateHalfVCol from '@/layouts/utils/CreateHalfVCol.vue'
import { getUserTypeName } from '@/views/users/useStore'
import { axios } from '@axios'
import corp from '@corp'
import { lengthValidator, passwordValidator, passwordValidatorV2, requiredValidatorV2 } from '@validators'

const alert = <any>(inject('alert'))
const snackbar = <any>(inject('snackbar'))
const errorHandler = <any>(inject('$errorHandler'))

const visible = ref(false)

const vForm = ref()
const user_type = ref(0)
const user_id = ref(0)
const password = ref()
const is_show = ref(false)

const show = (_user_id: number, _user_type: number) => {
    if( _user_type !== 3) {
        user_id.value = _user_id
        user_type.value = _user_type
        visible.value = true
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
            const r = await axios.post(`/api/v1/manager/${path}/${user_id.value}/password-change`, { user_pw: password.value })
            snackbar.value.show('성공하였습니다.', 'success')
        }
        catch (e: any) {
            snackbar.value.show(e.response.data.message, 'error')
            const r = errorHandler(e)
        }
        visible.value = false
    }
}

const handleEvent = (event: KeyboardEvent) => {
    event.preventDefault()
    submit()
}

const passwordRules = computed(() => {
    if(user_type.value === 0) {
        if(corp.pv_options.free.secure['mcht_pw_level'] === 0)
            return [requiredValidatorV2(password.value, '패스워드')]
        else if(corp.pv_options.free.secure['mcht_pw_level'] === 1)
            return [requiredValidatorV2(password.value, '패스워드'), lengthValidator(password.value, 8)]
        else if(corp.pv_options.free.secure['mcht_pw_level'] === 2)
            return [requiredValidatorV2(password.value, '패스워드'), passwordValidator]
    }
    else if(user_type.value === 1)
        return [requiredValidatorV2(password, '새 패스워드'), passwordValidator]
    else
        return [requiredValidatorV2(password, '새 패스워드'), passwordValidatorV2]
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
                        <CreateHalfVCol :mdl="4" :mdr="8">
                            <template #name>
                                <span style="line-height: 2.5em;">새 패스워드 입력</span>
                            </template>
                            <template #input>
                                <VTextField v-model="password" counter prepend-inner-icon="tabler-lock"
                                    :rules="passwordRules"
                                    :append-inner-icon="is_show ? 'tabler-eye' : 'tabler-eye-off'"
                                    :type="is_show ? 'text' : 'password'" persistent-placeholder
                                    @click:append-inner="is_show = !is_show" 
                                    @keydown.enter="handleEvent"
                                    autocomplete />
                            </template>
                        </CreateHalfVCol>
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
