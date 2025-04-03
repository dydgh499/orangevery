<script lang="ts" setup>
import { inputFormater } from '@/@core/utils/formatters';
import SpecifiedTimeDisablePaymentTr from '@/views/merchandises/specified-time-disable-payments/SpecifiedTimeDisablePaymentTr.vue';
import { useRequestStore } from '@/views/request';
import type { Merchandise, SpecifiedTimeDisablePayment } from '@/views/types';
import { isAbleModiy } from '@axios';
import { requiredValidatorV2 } from '@validators';

interface Props {
    item: Merchandise,
}
const props = defineProps<Props>()
const { setNullRemove } = useRequestStore()
const { formatTime } = inputFormater()

const specified_time_disable_limit_payments = reactive<SpecifiedTimeDisablePayment[]>(props.item.specified_time_disable_limit_payments || [])
const addNewSpecifiedTimeDisablePayment = () => {
    const specified_time_disable_limit_payment = <SpecifiedTimeDisablePayment>({
        id: 0,
        mcht_id: props.item.id,
        disable_s_tm: "00:00:00",
        disable_e_tm: "00:00:00",
        disable_type: 0,
    })
    specified_time_disable_limit_payments.push(specified_time_disable_limit_payment)
}

const formatDisableStm = computed(() => {
    props.item.single_payment_limit_s_tm = formatTime(props.item.single_payment_limit_s_tm)    
})
const formatDisableEtm = computed(() => {
    props.item.single_payment_limit_e_tm = formatTime(props.item.single_payment_limit_e_tm)    
})

watchEffect(() => {
    setNullRemove(specified_time_disable_limit_payments)
})
</script>
<template>
    <div>
        <VRow cols="12">
            <VCol :md="6" :cols="12">
                <VCardTitle>지정시간 결제제한</VCardTitle>
            </VCol>
        </VRow>
        <VRow style="margin-bottom: 1em;">
            <VCol :md="6" :cols="12">
                <VRow no-gutters style="align-items: center;" v-if="isAbleModiy(props.item.id)">
                    <VCol md="6" cols="7">
                        <span>단건 결제한도 하향금</span>
                    </VCol>
                    <VCol md="6" cols="5">
                        <div class="flex-container">
                            <VTextField 
                                v-model="props.item.specified_time_disable_limit" 
                                variant='underlined'
                                type="number" 
                                suffix="만원"
                                :rules="[requiredValidatorV2(props.item.specified_time_disable_limit, '단건 결제한도')]" 
                                style="max-width: 120px;margin-right: 1em;"
                            />
                        </div>
                    </VCol>
                </VRow>
                <VRow no-gutters style="align-items: center;" v-else>
                    <VCol md="6" cols="7">
                        <b>단건 결제한도 하향금</b>
                    </VCol>
                    <VCol md="6" cols="5">
                        <span>{{ props.item.specified_time_disable_limit?.toLocaleString()}} 만원</span>
                    </VCol>
                </VRow>
            </VCol>
            <VCol :md="6" :cols="12">
                <VRow no-gutters style="align-items: center;" v-if="isAbleModiy(props.item.id)">
                    <VCol md="6" cols="5" >
                        <span>하향적용시간</span>
                    </VCol>
                    <VCol md="6" cols="7">
                        <div class="flex-container" style="align-items: center;">
                            <VTextField 
                            variant='underlined'
                            v-model="props.item.single_payment_limit_s_tm" 
                                @input="formatDisableStm"
                                placeholder="시작시간"
                            />
                            <span style="margin: 0 0.5em;">~</span>
                            <VTextField 
                                variant='underlined'
                                v-model="props.item.single_payment_limit_e_tm" 
                                @input="formatDisableEtm"
                                placeholder="종료시간"
                            />
                        </div>
                    </VCol>
                </VRow>
                <VRow no-gutters style="align-items: center;" v-else>
                    <VCol md="6" cols="5">
                        <b>하향적용시간</b>
                    </VCol>
                    <VCol md="6" cols="7">
                        <span>{{ props.item.single_payment_limit_s_tm }}</span>
                        <span style="margin: 0 0.5em;">~</span>
                        <span>{{ props.item.single_payment_limit_e_tm }}</span>
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
                    <th scope="col" class='list-square' v-if="isAbleModiy(props.item.id)">추가/수정</th>
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
        <VRow v-show="Boolean(props.item.id != 0)" v-if="isAbleModiy(props.item.id)">
            <VCol class="d-flex gap-4">
                <VBtn type="button" style="margin-left: auto;" @click="addNewSpecifiedTimeDisablePayment()">
                    추가하기
                    <VIcon end icon="tabler-plus" />
                </VBtn>
            </VCol>
        </VRow>
    </div>
</template>
<style scoped>
:deep(.v-table__wrapper) {
  block-size: auto !important;
}

.flex-container {
  display: flex;
  align-items: center;
  justify-content: flex-start;
}
</style>
