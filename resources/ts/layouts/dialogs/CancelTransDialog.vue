<script setup lang="ts">
import type { Transaction } from '@/views/types'
import { requiredValidator } from '@validators'
import { cloneDeep } from 'lodash';
import { axios } from '@axios';

const store = <any>(inject('store'))
const alert = <any>(inject('alert'))
const snackbar = <any>(inject('snackbar'))
const errorHandler = <any>(inject('$errorHandler'))

const visible = ref(false)
const trans = ref<Transaction>()

const vForm = ref()
const cxl_dt = ref()
const cxl_tm = ref()

const show = (item: Transaction) => {
    trans.value = item
    visible.value = true
}

const submit = async() => {
    const is_valid = await vForm.value.validate();
    if (is_valid.valid && await alert.value.show('정말 취소 매출을 생성하시겠습니까?')) {
        const p = cloneDeep(trans.value)
        p['cxl_dt'] = cxl_dt.value
        p['cxl_tm'] = cxl_tm.value
        try {
            const r = await axios.post('/api/v1/manager/transactions/cancel', p)
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
    <VDialog v-model="visible" max-width="600">
        <!-- Dialog close btn -->
        <DialogCloseBtn @click="visible = false" />
        <!-- Dialog Content -->
        <VCard title="취소매출생성">
            <VCardText>
                <VForm ref="vForm">
                    <VCol cols="12">
                    <VRow no-gutters>
                        <VCol cols="12" md="4">
                            <label>취소시간</label>
                        </VCol>
                        <VCol cols="12" md="4">
                            <VTextField v-model="cxl_dt" type="date" :rules="[requiredValidator]"/>
                        </VCol>
                        <VCol cols="12" md="4">
                            <VTextField v-model="cxl_tm" type="time" step="1" :rules="[requiredValidator]" />
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
                    생성
                </VBtn>
            </VCardText>
        </VCard>
    </VDialog>
</template>
