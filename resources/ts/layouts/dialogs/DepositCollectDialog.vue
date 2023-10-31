<script setup lang="ts">
import { settlementFunctionCollect } from '@/views/transactions/settle/Settle'
import CreateHalfVCol from '@/layouts/utils/CreateHalfVCol.vue'
import { requiredValidator } from '@validators'
import { Settle } from '@/views/types'


const alert = <any>(inject('alert'))
const store = <any>(inject('store'))
const { settleCollect } = settlementFunctionCollect(store)

const vForm = ref()
const amount = ref()
const visible = ref(false)
const settle = ref<Settle>()


const show = (item: Settle) => {
    settle.value = item
    visible.value = true
}
const submit = async () => {
    const is_valid = await vForm.value.validate();
    if (is_valid.valid && await alert.value.show('정말 '+amount.value.toLocaleString()+'원을 출금 하시겠습니까?')) {

    }
}
const depositLimitValidator = (value: unknown) => {
    return (settle.value?.settle.deposit || 0) >= amount.value || '출금 가능 금액을 초과하였습니다.'
}
//settleCollect(settle.mcht_name, props.item)
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
                
                <span style="line-height: 2.5em;">출금 가능 금액: {{ settle?.settle.deposit.toLocaleString() }}원</span>
                <VForm ref="vForm">
                    <VCol cols="12">
                        <CreateHalfVCol :mdl="4" :mdr="8">
                            <template #name>
                                <span style="line-height: 2.5em;">출금 금액 입력</span>
                            </template>
                            <template #input>
                                <VTextField v-model="amount" prepend-inner-icon="fa6-solid:money-bill-transfer"  type="number"
                                    :rules="[requiredValidator, depositLimitValidator]" persistent-placeholder  autocomplete />
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
                    출금하기
                </VBtn>
            </VCardText>
        </VCard>
    </VDialog>
</template>
