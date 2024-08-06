<script lang="ts" setup>
import SpecifiedTimeDisablePaymentTr from '@/views/merchandises/specified-time-disable-payments/SpecifiedTimeDisablePaymentTr.vue'
import { useRequestStore } from '@/views/request'
import type { Merchandise, SpecifiedTimeDisablePayment } from '@/views/types'
import { requiredValidatorV2 } from '@validators'

interface Props {
    item: Merchandise,
}
const props = defineProps<Props>()
const { setNullRemove } = useRequestStore()
const specified_time_disable_limit_payments = reactive<SpecifiedTimeDisablePayment[]>(props.item.specified_time_disable_limit_payments || [])
const addNewSpecifiedTimeDisablePayment = () => {
    const specified_time_disable_limit_payment = <SpecifiedTimeDisablePayment>({
        id: 0,
        mcht_id: props.item.id,
        disable_s_tm: null,
        disable_e_tm: null,
        disable_type: 0,
    })
    specified_time_disable_limit_payments.push(specified_time_disable_limit_payment)
}
watchEffect(() => {
    setNullRemove(specified_time_disable_limit_payments)
})
</script>
<template>
    <VRow>
        <VCol :md="6" :cols="12">
            <VCardTitle>지정시간 결제제한</VCardTitle>
        </VCol>
    </VRow>
    <VRow style="margin-bottom: 1em;">
        <VCol :md="5" :cols="12">
            <VRow no-gutters style="align-items: center;">
                <VCol md="7" cols="7">단건 결제한도 하향 설정</VCol>
                <VCol md="5" cols="5">
                    <VTextField v-model="props.item.specified_time_disable_limit" type="number" suffix="만원" label="단건 결제한도"
                        :rules="[requiredValidatorV2(props.item.specified_time_disable_limit, '단건 결제한도')]" 
                        style="max-width: 120px;margin-right: 1em;"/>
                </VCol>
            </VRow>
        </VCol>
        <VCol :md="7" :cols="12">
            <VRow no-gutters style="align-items: center;">
                <VCol md="3" cols="3" :style="$vuetify.display.smAndDown ? 'margin-bottom:1em;' : ''">적용시간</VCol>
                <VCol md="9" cols="12">
                    <div class="flex-container">
                        <VTextField v-model="props.item.single_payment_limit_s_tm" type="time" label="시작시간"
                            style="max-width: 150px;"/>                        
                        <span style="margin: 0 0.5em;">~</span>
                        <VTextField v-model="props.item.single_payment_limit_e_tm" type="time" label="종료시간"
                            style="max-width: 150px;"/>
                    </div>
                </VCol>
            </VRow>
        </VCol>
    </VRow>
    
    <VTable style="width: 100%;margin-bottom: 1em;text-align: center;">
        <thead>
            <tr>
                <th scope="col" class='list-square'>No.</th>
                <th scope="col" class='list-square'>제한타입</th>
                <th scope="col" class='list-square'>시작시간</th>
                <th scope="col" class='list-square'>종료시간</th>
                <th scope="col" class='list-square'>추가/수정</th>
            </tr>
        </thead>
        <tbody>
            <SpecifiedTimeDisablePaymentTr v-for="(item, index) in specified_time_disable_limit_payments"
                :key="item.id" style="margin-top: 1em;" :item="item" :index="index" />
        </tbody>
        <tfoot v-show="Boolean(props.item.id == 0)">
            <tr>
                <td colspan="4" class="text-center">
                    가맹점을 추가하신 후 사용 가능합니다.
                </td>
            </tr>
        </tfoot>
    </VTable>
    <VRow v-show="Boolean(props.item.id != 0)">
        <VCol class="d-flex gap-4">
            <VBtn type="button" style="margin-left: auto;" @click="addNewSpecifiedTimeDisablePayment()">
                세팅정보 신규추가
                <VIcon end icon="tabler-plus" />
            </VBtn>
        </VCol>
    </VRow>
</template>
<style scoped>
:deep(.v-table__wrapper) {
  block-size: auto !important;
}

.flex-container {
  display: flex;
  align-items: center;
  justify-content: center;
}
</style>
