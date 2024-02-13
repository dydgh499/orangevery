<script setup lang="ts">
import { requiredValidator } from '@validators'
import { axios } from '@axios'
import CreateHalfVCol from '@/layouts/utils/CreateHalfVCol.vue'
import corp from '@corp'

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
        snackbar.value.show(`${corp.pv_options.auth.levels.dev_name}는 패스워드를 변경할 수 없습니다.`, 'warning')
    }
}

const submit = async () => {
    const is_valid = await vForm.value.validate();
    if (is_valid.valid && await alert.value.show('정말 패스워드를 변경하시겠습니까?')) {
        let page = ''
        if (user_type.value == 0)
            page = 'merchandises'
        else if (user_type.value == 1)
            page = 'salesforces'
        else if (user_type.value == 2)
            page = 'services/operators'
        try {
            const r = await axios.post('/api/v1/manager/' + page + '/'+user_id.value+'/password-change', { user_pw: password.value })
            snackbar.value.show('성공하였습니다.', 'success')
        }
        catch (e: any) {
            snackbar.value.show(e.response.data.message, 'error')
            const r = errorHandler(e)
        }
        visible.value = false
    }
}

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
                                    :rules="[requiredValidator]"
                                    :append-inner-icon="is_show ? 'tabler-eye' : 'tabler-eye-off'"
                                    :type="is_show ? 'text' : 'password'" persistent-placeholder
                                    @click:append-inner="is_show = !is_show" autocomplete />
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
