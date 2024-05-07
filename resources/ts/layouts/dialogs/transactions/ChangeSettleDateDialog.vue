<script setup lang="ts">
import { axios } from '@axios'
import { requiredValidatorV2 } from '@validators'

const store = <any>(inject('store'))
const alert = <any>(inject('alert'))
const snackbar = <any>(inject('snackbar'))
const errorHandler = <any>(inject('$errorHandler'))

const visible = ref(false)
const vForm = ref()
const settle_dt = ref()
const selected = ref(<number[]>([]))

const show = (_selected: number[]) => {
    selected.value = _selected
    visible.value = true
}

const submit = async() => {
    const is_valid = await vForm.value.validate();
    if (is_valid.valid && await alert.value.show('정말 정산일을 변경하시겠습니까?')) {
        try {
            const r = await axios.post('/api/v1/manager/transactions/change-settle-date', {
                settle_dt: settle_dt.value,
                selected: selected.value
            })
            snackbar.value.show('성공하였습니다.', 'success')
            store.setTable()
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
    <VDialog v-model="visible" max-width="500">
        <!-- Dialog close btn -->
        <DialogCloseBtn @click="visible = false" />
        <!-- Dialog Content -->
        <VCard title="정산일 변경">
            <VCardText>
                <VForm ref="vForm">
                    <VCol cols="12">
                        <VRow no-gutters style="align-items: center;">
                            <VCol cols="12" md="4">
                                <label>날짜선택</label>
                            </VCol>
                            <VCol cols="12" md="5">
                                <VTextField v-model="settle_dt" type="date" :rules="[requiredValidatorV2(settle_dt, '정산일')]"/>
                            </VCol>
                        </VRow>
                    </VCol>
                    <br>
                    <VCol cols="12">
                        <VRow>
                            선택하신 {{ selected.length }}건의 정산일자를 {{ settle_dt }}로 변경합니다.
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
