<script setup lang="ts">
import { inputFormater } from '@/@core/utils/formatters';
import { axios } from '@/plugins/axios';
import { requiredValidatorV2 } from '@validators';

const vForm = ref()
const visible = ref(false)

const alert = <any>(inject('alert'))
const snackbar = <any>(inject('snackbar'))
const errorHandler = <any>(inject('$errorHandler'))

const mcht_id = ref(0)
const {
    phone_num_format,
    phone_num,
    formatPhoneNum,
} = inputFormater()

const show = (_mcht_id: number) => {
    visible.value = true
    mcht_id.value = _mcht_id
    phone_num.value = ''
    phone_num_format.value = ''
}

const submit = async() => {
    const is_valid = await vForm.value.validate()
    if (is_valid.valid) {
        if(await alert.value.show('정말 초기화하시겠습니까?')) {
            axios.post(`/api/v1/bonaejas/pay-verfication-init`, {
                mcht_id: mcht_id.value,
                phone_num: phone_num.value,
            }).then(r => {
                visible.value = false
                snackbar.value.show('성공하였습니다.', 'success')
            })
            .catch(async e => {
                snackbar.value.show(e.response.data.message, 'error')
                const r = errorHandler(e)
            })
        }
    }
}

defineExpose({
    show
})
</script>
<template>
    <VDialog v-model="visible" persistent max-width="600">
        <DialogCloseBtn @click="visible = !visible" />
        <VCard class="pa-5 pa-sm-8">
            <VCardText>
                <VForm ref="vForm">
                    <VRow style="align-items: center;">
                        <VCol md="4" cols="12"><b>초기화할 휴대폰번호</b></VCol>
                        <VCol md="8" cols="12">
                            <div style="display: flex; align-items: center;">
                                <VTextField 
                                    v-model="phone_num_format" 
                                    @input="formatPhoneNum"
                                    variant="underlined"
                                    prepend-icon="tabler-device-mobile" placeholder="연락처를 입력해주세요"
                                    :rules="[requiredValidatorV2(phone_num, '연락처')]" 
                                />
                            </div>
                        </VCol>
                    </VRow>
                </VForm>
            </VCardText>
            <VCardText class="d-flex justify-end gap-3 flex-wrap">
                <VBtn @click="submit">
                    확인
                </VBtn>
            </VCardText>
        </VCard>
    </VDialog>
</template>
