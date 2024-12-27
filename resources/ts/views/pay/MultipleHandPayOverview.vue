<script setup lang="ts">
import { inputFormater } from '@/@core/utils/formatters'
import CreateHalfVCol from '@/layouts/utils/CreateHalfVCol.vue'
import MultipleHandPayForm from '@/views/pay/multiple-hand-pay/MultipleHandPayForm.vue'
import type { Merchandise, PayModule } from '@/views/types'
import { requiredValidatorV2 } from '@validators'
import { VForm } from 'vuetify/components'
import { multipleHandPaySequence } from './multiple-hand-pay/multipleHandPay'

interface Props {
    pay_module: PayModule,
    merchandise: Merchandise,
}

const props = defineProps<Props>()
const snackbar = <any>(inject('snackbar'))
const salesslip = <any>(inject('salesslip'))
const is_verify_sms = ref(false)
const vForm = ref<VForm>()

const {
    phone_num_format,
    phone_num,
    formatPhoneNum,
} = inputFormater()

const {
    hand_pay_info,
    hand_pay_infos,
    full_processes,
    valid_total_amount,
    purchaseStart,
    addNewHandPay,
    init,
} = multipleHandPaySequence()

    // 다중 결제 시작
const pays = async () => {
    const common_valid = await vForm.value?.validate()
    const total_amount = hand_pay_infos.value.reduce((sum: number, obj: { amount: any }) => sum + Number(obj.amount), 0)

    if(valid_total_amount.value !== total_amount) {
        snackbar.value.show('결제금액은 총 ' + valid_total_amount.value.toLocaleString() + '원 이어야합니다.', 'error')
        return
    }
    if(common_valid?.valid == false) {
        snackbar.value.show('공통 결제정보를 확인해주세요.', 'error')
        return
    }
    await purchaseStart(total_amount, props.merchandise)
}

const updateToken = (value : string) => {
    if(value.length > 10) {
        is_verify_sms.value = true
    }
}

const isShowMobileVerification = computed(() => {
    return props.pay_module.pay_window_secure_level >= 3 && is_verify_sms.value === false
})

watchEffect(async () => {
    if (hand_pay_info.value.buyer_name && hand_pay_info.value.buyer_phone) {
        // watchEffect가 잡히지 않는 이유?
    }
    let is_valid = await vForm.value?.validate()
    hand_pay_info.value.status_icon = is_valid?.valid ? 'line-md:check-all' : 'line-md:emoji-frown-twotone'
    hand_pay_info.value.status_color = is_valid?.valid ? 'success' : 'error'    
})
watchEffect(() => {
    hand_pay_info.value.buyer_phone = phone_num.value
})
onMounted(() => {
    init()
})

</script>
<template>
    <section>
    <VDivider />
    <CreateHalfVCol :mdl="6" :mdr="6" style="align-items: baseline !important;">
        <template #name>
            <AppCardActions :actionCollapsed="true" class="common-field" style="margin-bottom: 1em;">
                <template #title>
                    <div>
                        <span>공통 결제정보</span>
                        <VIcon size="24" :icon=hand_pay_info.status_icon :color=hand_pay_info.status_color
                            style="float: inline-end;" />
                    </div>
                </template>
                <VDivider/>
                <VForm ref="vForm">
                    <VCol>
                        <VRow>
                            <VCol md="12" cols="12" style="padding-bottom: 0;">
                                <VRow no-gutters style="min-height: 4em;">
                                    <VCol cols="4" :md="2">
                                        <label>상품명</label>
                                    </VCol>
                                    <VCol cols="8" :md="10">
                                        <VTextField v-model="hand_pay_info.item_name" name="item_name"
                                            prepend-icon="tabler:shopping-bag"
                                            maxlength="100" 
                                            counter
                                            variant="underlined"
                                            :rules="[requiredValidatorV2(hand_pay_info.item_name, '상품명')]" 
                                            placeholder="상품명을 입력해주세요" />
                                    </VCol>
                                </VRow>
                            </VCol>
                        </VRow>

                        <VRow>
                            <VCol md="12" cols="12" style="padding: 0 12px;">
                                <VRow no-gutters style="min-height: 4em;">
                                    <VCol cols="4" :md="2">
                                        <label>구매자명</label>
                                    </VCol>
                                    <VCol cols="8" :md="10">
                                        <VTextField v-model="hand_pay_info.buyer_name" name="buyer_name"
                                            variant="underlined"
                                            placeholder="구매자명을 입력해주세요" :rules="[requiredValidatorV2(hand_pay_info.buyer_name, '구매자명')]" 
                                            prepend-icon="tabler-user" />
                                    </VCol>
                                </VRow>
                            </VCol>
                        </VRow>
                        <VRow>
                            <VCol md="12" cols="12" style="padding: 0 12px;">
                                <VRow no-gutters style="min-height: 4em;">
                                    <VCol cols="4" :md="2">
                                        <label>연락처</label>
                                    </VCol>
                                    <VCol cols="8" :md="10">
                                        <VTextField 
                                            v-model="phone_num_format" 
                                            @input="formatPhoneNum"
                                            variant="underlined"
                                            prepend-icon="tabler-device-mobile" placeholder="구매자 연락처를 입력해주세요"
                                            :rules="[requiredValidatorV2(hand_pay_info.buyer_phone, '구매자 연락처')]" 
                                        />
                                    </VCol>
                                </VRow>
                            </VCol>
                        </VRow>

                    </VCol>
                    
                    <VCol cols="12">
                        <VRow>
                            <VCol cols="6">
                                <span>총 입력금액</span>
                                <b style="margin-left: 0.5em;">{{ hand_pay_infos.reduce((sum, obj) => sum + Number(obj.amount), 0).toLocaleString() }}</b>원
                                <br>
                                <span>총 결제금액</span>
                                <b style="margin-left: 0.5em;">{{ valid_total_amount.toLocaleString() }}</b>원
                            </VCol>
                            <VCol cols="6">
                                <VBtn @click="addNewHandPay(pay_module)" color="primary" style="width: 100%;float: inline-end;">결제정보 추가</VBtn>
                            </VCol>
                        </VRow>
                    </VCol>
                </VForm>
            </AppCardActions>
        </template>
        <template #input>
            <MultipleHandPayForm v-for="(item, index) in hand_pay_infos" :key="index" :hand_pay_info="hand_pay_infos[index]"
                :pay_module="props.pay_module" :index="index" style="margin-bottom: 1em;" />
        </template>
    </CreateHalfVCol>
    <VTable class="text-no-wrap" style="margin-bottom: 1em;" v-if="full_processes.length > 0" id="process-table">
        <thead>
            <tr>
                <th scope="col" class='list-square' style="width: 150px;"><b>결제모듈</b></th>
                <th scope="col" class='list-square' style="width: 50px;">
                    <VIcon size="24" icon="tabler:arrow-big-right-filled" color="primary" />
                </th>
                <th scope="col" class='list-square' style="width: 300px;"><b>승인</b></th>
                <th scope="col" class='list-square' style="width: 50px;">
                    <VIcon size="24" icon="tabler:arrow-big-right-filled" color="error" />
                </th>
                <th scope="col" class='list-square' style="width: 300px;"><b>취소</b></th>
            </tr>
        </thead>
        <tbody>
            <tr v-for="(_item, _index) in full_processes" :key="_index">
                <td class='list-square'><b>결제정보 {{ (_index+1) }}</b></td>
                <td class='list-square'>
                    <VIcon size="24" icon="tabler:arrow-big-right-filled" color="primary" />
                </td>
                <td class='list-square'>
                    <template v-if="Object.keys(_item.trx_result).length == 0">
                        <VIcon size="24" icon="svg-spinners:bars-fade" class="process-icon" />
                        <br>
                        <span>결제중 입니다...</span>
                    </template>
                    <template v-else>
                        <VIcon size="24"
                            :icon="_item.trx_result.result_cd == '0000' ? 'line-md:check-all' : 'line-md:emoji-frown-twotone'"
                            :color="_item.trx_result.result_cd == '0000' ? 'success' : 'error'" class="process-icon" />
                        <br>
                        <span v-html="_item.trx_result.result_msg"></span>
                        <br>
                        <template v-if="_item.trx_result.result_cd == '0000'">
                            <VBtn @click="salesslip.show(_item.trx_result)">승인 영수증 확인</VBtn>
                        </template>
                    </template>
                </td>
                <td class='list-square'>
                    <span v-if="_item.cxl_process != null">
                        <VIcon size="24" icon="tabler:arrow-big-right-filled" color="error" class="process-icon" />
                    </span>
                </td>
                <td class='list-square'>
                    <span v-if="_item.cxl_process != null">
                        <template v-if="Object.keys(_item.cxl_result).length == 0">
                            <VIcon size="24" icon="svg-spinners:bars-fade" class="process-icon" />
                            <br>
                            <span>취소중 입니다...</span>
                        </template>
                        <template v-else>
                            <VIcon size="24"
                                :icon="_item.cxl_result.result_cd == '0000' ? 'line-md:check-all' : 'line-md:emoji-frown-twotone'"
                                :color="_item.cxl_result.result_cd == '0000' ? 'success' : 'error'" class="process-icon" />
                            <br>
                            <span v-html="_item.cxl_result.result_msg"></span>
                            <br>
                            <template v-if="_item.cxl_result.result_cd == '0000'">
                                <VBtn @click="salesslip.show(_item.cxl_result)" color="error">취소 영수증 확인</VBtn>
                            </template>
                        </template>
                    </span>
                </td>
            </tr>
        </tbody>
    </VTable>
    <VCard>
        <VCardText>
            <MobileVerification
                v-if="isShowMobileVerification"
                @update:token="updateToken($event)" :phone_num="hand_pay_info.buyer_phone" 
                :merchandise="props.merchandise"/>
            <VCol cols="12" style="padding: 0;" v-else>
                <VBtn block @click="pays()">
                    결제하기
                </VBtn>
            </VCol>
        </VCardText>
    </VCard>
    </section>

</template>
<style scoped>
.process-icon {
  margin-block: 0.5em;
  margin-inline: 0;
}

#process-table {
  margin-inline: auto;
}

:deep(.v-card-item) {
  padding: 18px !important;
}

:deep(.v-table__wrapper) {
  block-size: auto !important;
}

@media (min-width: 900px) {
  .common-field {
    margin-inline-end: 1em !important;
  }

  :deep(.common-field) {
    margin-inline-end: 1em !important;
  }
}

:deep(.v-row) {
  align-items: center;
}
</style>
