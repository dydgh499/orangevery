<script lang="ts" setup>
import FeeBookDialog from '@/layouts/dialogs/users/FeeBookDialog.vue'
import PasswordCheckDialog from '@/layouts/dialogs/users/PasswordCheckDialog.vue'
import BooleanRadio from '@/layouts/utils/BooleanRadio.vue'
import type { Merchandise, UnderAutoSetting } from '@/views/types'
import { amountValidator, requiredValidatorV2 } from '@validators'

import UnderAutoSettingDialog from '@/layouts/dialogs/users/UnderAutoSettingDialog.vue'
import BaseQuestionTooltip from '@/layouts/tooltips/BaseQuestionTooltip.vue'
import FeeChangeBtn from '@/views/merchandises/FeeChangeBtn.vue'
import ProductCard from '@/views/merchandises/products/ProductCard.vue'
import RegularCreditCard from '@/views/merchandises/regular-credit-cards/RegularCreditCard.vue'
import SpecifiedTimeDisablePaymentCard from '@/views/merchandises/specified-time-disable-payments/SpecifiedTimeDisablePaymentCard.vue'

import { merchant_statuses, MerchantStatusColor, tax_category_types } from '@/views/merchandises/useStore'
import { useRequestStore } from '@/views/request'
import { useSalesFilterStore } from '@/views/salesforces/useStore'
import { StatusColorSetter } from '@/views/searcher'
import { useStore } from '@/views/services/pay-gateways/useStore'
import { getIndexByLevel, getLevelByIndex, getUserLevel, isAbleModiy, user_info } from '@axios'
import corp from '@corp'
import { autoUpdateMerchandiseParentSalesInfo } from '../salesforces/overlap'
import { merchandiseCreateAuth, useFeeCalculatorStore } from './feeCalculatorStore'

interface Props {
    item: Merchandise,
}

const props = defineProps<Props>()
const { post } = useRequestStore()
const { sales, all_sales, initAllSales, sales_apply_histories, hintSalesApplyFee, hintSalesSettleFee, hintSalesSettleTaxTypeText, hintSalesSettleTotalFee } = useSalesFilterStore()
const { getSalesSettleInfo } = useFeeCalculatorStore()
const { cus_filters } = useStore()

const alert = <any>(inject('alert'))
const levels = corp.pv_options.auth.levels
const contact_num_format = ref('')

const feeBookDialog = ref()
const underAutoSetting = ref()
const passwordCheckDialog = ref()

provide('feeBookDialog', feeBookDialog)

const { isSalesModifyValidate, isSalesAddTIDMode } = merchandiseCreateAuth()

const setSalesUnderAutoSetting = async (my_level: number) => {
    const setSalesAutoInfo = (my_level: number, under_auto_setting: UnderAutoSetting) => {
        const sales_key = 'sales' + my_level   
        props.item[sales_key+'_id'] = under_auto_setting.sales_id
        props.item[sales_key+'_fee'] = under_auto_setting.sales_fee
    }

    const salesforce = sales[my_level].value.find(obj => obj.id === props.item['sales'+my_level+'_id'])
    if(salesforce?.under_auto_settings?.length ) {
        const idx = await underAutoSetting.value.show(salesforce.under_auto_settings)
        if(idx !== -1)
            setSalesAutoInfo(my_level, salesforce.under_auto_settings[idx])        
    }
    else {
        // 일괄적용
        const history = sales_apply_histories.find(obj => obj.sales_id === props.item['sales'+my_level+'_id'])
        if(history)
            props.item['sales'+my_level+'_fee'] = (history.trx_fee * 100).toFixed(3)
    }
}

const setSettleHoldClear = async () => {
    if(await alert.value.show('정말 지급보류 하시겠습니까?')) {
        await post('/api/v1/manager/merchandises/'+props.item.id+'/set-settle-hold', {
            settle_hold_s_dt: props.item.settle_hold_s_dt,
            settle_hold_reason: props.item.settle_hold_reason,
        }, true)
    }
}

const clearSettleHoldClear = async () => {
    const user_pw = await passwordCheckDialog.value.show()
    if(user_pw !== '' && await alert.value.show('정말 지급보류를 해제하시겠습니까?')) {
        await post('/api/v1/manager/merchandises/'+props.item.id+'/clear-settle-hold', {
            user_pw: user_pw,
        }, true)
        props.item.settle_hold_s_dt = ''
        props.item.settle_hold_reason = ''
    }
}

const formatContactNum = computed(() => {
    let raw_value = contact_num_format.value.replace(/\D/g, '');
    props.item.contact_num = raw_value
    // 휴대폰 번호 마스킹
    if(raw_value.length === 8)
        contact_num_format.value = raw_value.replace(/(\d{4})(\d{4})/, '$1-$2')
    else if(raw_value.startsWith("02") && (raw_value.length === 9 || raw_value.length === 10))
        contact_num_format.value = raw_value.replace(/(\d{2})(\d{3,4})(\d{4})/, '$1-$2-$3')
    else if(!raw_value.startsWith("02") && (raw_value.length === 10 || raw_value.length === 11))
        contact_num_format.value = raw_value.replace(/(\d{3})(\d{3,4})(\d{4})/, '$1-$2-$3')
})

const MerchandiseTrxFeeValidate = computed(() => {
    if(corp.pv_options.paid.sales_parent_structure) {
        const settle_info = getSalesSettleInfo(props.item)
        return [requiredValidatorV2(props.item.trx_fee, '가맹점 수수료율'), amountValidator(props.item.trx_fee, '가맹점 수수료율', settle_info.sales_total_fee)]
    }
    else {
        if(getUserLevel() >= 35) {
            const settle_info = getSalesSettleInfo(props.item)
            return [requiredValidatorV2(props.item.trx_fee, '가맹점 수수료율'), amountValidator(props.item.trx_fee, '가맹점 수수료율', settle_info.sales_total_fee)]
        }
        else
            return [requiredValidatorV2(props.item.trx_fee, '가맹점 수수료율')]
    }
})

onMounted(() => {
    initAllSales()
    watchEffect(() => {
        // 수정가능, 추가상태, 영업점일 경우
        if(props.item.id === 0 && isAbleModiy(props.item.id)) {
            if(getUserLevel() > 10 && getUserLevel() < 35) {
                if(corp.pv_options.paid.sales_parent_structure)
                    autoUpdateMerchandiseParentSalesInfo(props.item, all_sales)
                else {
                    const idx = getLevelByIndex(getUserLevel())
                    props.item[`sales${idx}_id`] = user_info.value.id
                }
            }        
        }
    })

    watchEffect(() => {
        contact_num_format.value = props.item.contact_num ?? ''
    })
})
</script>
<template>
    <VRow>
        <!-- 👉 개인정보 -->
        <VCol cols="12" md="6">
            <VCard>
                <VCardItem>
                    <VCardTitle>가맹점정보</VCardTitle>
                    <VRow class="pt-5">
                        <VCol cols="12">
                            <VRow>
                                <VCol cols="12" md="6">
                                    <VRow no-gutters style="align-items: center;" v-if="isAbleModiy(props.item.id)">
                                        <VCol cols="4">* 가맹점 상호</VCol>
                                        <VCol md="8">
                                            <VTextField v-model="props.item.mcht_name" prepend-inner-icon="tabler-building-store"
                                            placeholder="상호를 입력해주세요" persistent-placeholder :rules="[requiredValidatorV2(props.item.mcht_name, '가맹점 상호')]" />
                                        </VCol>
                                    </VRow>
                                    <VRow v-else>
                                        <VCol class="font-weight-bold">가맹점 상호</VCol>
                                        <VCol md="8"><span>{{ props.item.mcht_name }}</span></VCol>
                                    </VRow>
                                </VCol>
                                <VCol cols="12" md="6">
                                    <VRow no-gutters style="align-items: center;" v-if="isAbleModiy(props.item.id)">
                                        <VCol cols="4">업종</VCol>
                                        <VCol md="8">
                                            <VTextField v-model="props.item.sector" prepend-inner-icon="tabler-building-store"
                                                placeholder="업종을 입력해주세요" persistent-placeholder />
                                        </VCol>
                                    </VRow>
                                    <VRow v-else>
                                        <VCol class="font-weight-bold">업종</VCol>
                                        <VCol md="8"><span>{{ props.item.sector }}</span></VCol>
                                    </VRow>
                                </VCol>
                            </VRow>
                        </VCol>
                        <VCol cols="12" v-if="corp.use_different_settlement">
                            <VRow>
                                <VCol cols="12" md="6">
                                    <VRow no-gutters style="align-items: center;" v-if="isAbleModiy(props.item.id)">
                                        <VCol cols="4">이메일</VCol>
                                        <VCol md="8"> 
                                            <VTextField v-model="props.item.email" prepend-inner-icon="material-symbols:mail"
                                                placeholder="이메일을 입력해주세요" persistent-placeholder>
                                                <VTooltip activator="parent" location="top" maxlength="50">
                                                    하위몰이 대표 이메일주소
                                                </VTooltip>
                                            </VTextField>
                                        </VCol>
                                    </VRow>
                                    <VRow v-else>
                                        <VCol class="font-weight-bold">이메일</VCol>
                                        <VCol md="8"><span>{{ props.item.email }}</span></VCol>
                                    </VRow>
                                </VCol>
                                <VCol cols="12" md="6">
                                    <VRow no-gutters style="align-items: center;" v-if="isAbleModiy(props.item.id)">
                                        <VCol cols="4">웹사이트 URL</VCol>
                                        <VCol md="8">
                                            <VTextField v-model="props.item.website_url" prepend-inner-icon="streamline:browser-website-1-solid"
                                                placeholder="웹사이트 URL 입력해주세요" persistent-placeholder maxlength="250">
                                                <VTooltip activator="parent" location="top">
                                                    하위몰이 없는경우 2차PG사 URL을 입력해주세요.
                                                </VTooltip>
                                            </VTextField>
                                        </VCol>
                                    </VRow>
                                    <VRow v-else>
                                        <VCol class="font-weight-bold">웹사이트 URL</VCol>
                                        <VCol md="8"><span>{{ props.item.website_url }}</span></VCol>
                                    </VRow>
                                </VCol>
                            </VRow>
                        </VCol>
                        <VCol cols="12">
                            <VRow>
                                <VCol cols="12" md="6">
                                    <VRow no-gutters style="align-items: center;" v-if="isAbleModiy(props.item.id)">
                                        <VCol cols="4">사업장 연락처</VCol>
                                        <VCol md="8">
                                            <VTextField 
                                                v-model="contact_num_format" 
                                                @input="formatContactNum"
                                                prepend-inner-icon="tabler-building-store" 
                                                placeholder="02-123-1234"
                                                maxlength=20
                                            >                                            
                                                <VTooltip activator="parent" location="top">
                                                    매출전표에 해당 번호가 표기됩니다.<br>(매출전표 판매자 정보: 가맹점 일 경우)
                                                </VTooltip>
                                            </VTextField>
                                        </VCol>
                                    </VRow>
                                    <VRow v-else>
                                        <VCol class="font-weight-bold">사업장 연락처</VCol>
                                        <VCol md="8"><span>{{ contact_num_format }}</span></VCol>
                                    </VRow>
                                </VCol>
                            </VRow>
                        </VCol>
                        <template v-if="getUserLevel() > 10">
                            <VDivider/>
                            <VCol cols="12">
                                <VCardTitle>영업점 수수료</VCardTitle>
                            </VCol>
                            <template v-for="i in 6" :key="i">
                                <VCol cols="12" v-if="levels['sales'+(6-i)+'_use'] && getUserLevel() >= getIndexByLevel(6-i)">
                                    <VRow v-if="isSalesModifyValidate(props.item.id, 6 - i)">
                                        <VCol cols="12" md="3">* {{ levels['sales'+(6-i)+'_name'] }}/수수료율</VCol>
                                        <VCol cols="6" :md="props.item.id ? 3 : 4">
                                            <VAutocomplete :menu-props="{ maxHeight: 400 }" v-model="props.item['sales'+(6-i)+'_id']"
                                                :items="sales[6-i].value"
                                                :label="levels['sales'+(6-i)+'_name'] + '선택'"
                                                item-title="sales_name" item-value="id" persistent-hint single-line prepend-inner-icon="ph:share-network"
                                                :hint="hintSalesApplyFee(props.item['sales'+(6-i)+'_id'])" @update:modelValue="setSalesUnderAutoSetting(6-i)" :readonly="getUserLevel() <= getIndexByLevel(6-i)"/>

                                                <VTooltip activator="parent" location="top" v-if="props.item['sales'+(6-i)+'_id']">
                                                    {{ sales[6-i].value.find(obj => obj.id === props.item['sales'+(6-i)+'_id'])?.sales_name }}
                                                </VTooltip>
                                        </VCol>
                                        <VCol cols="6" :md="props.item.id ? 3 : 4">
                                            <VTextField v-model="props.item['sales'+(6-i)+'_fee'] " type="number" suffix="%"
                                                :rules="[requiredValidatorV2(props.item['sales'+(6-i)+'_fee'], levels['sales'+(6-i)+'_name']+'수수료율')]" />

                                            <div style="font-size: 0.8em; font-weight: bold; text-align: center;" v-if="props.item['sales'+(6-i)+'_id']">
                                                <template v-if="corp.pv_options.paid.fee_input_mode === false">
                                                    <span>{{ hintSalesSettleFee(props.item, 6-i) }}</span>
                                                    <br>
                                                </template>
                                                <span>
                                                    ({{ hintSalesSettleTaxTypeText(props.item, 6-i, all_sales[(6-i)]) }})
                                                    = {{ hintSalesSettleTotalFee(props.item, 6-i, all_sales[(6-i)]) }}%
                                                </span>
                                            </div>
                                        </VCol>
                                        <FeeChangeBtn v-if="props.item.id" :level=getIndexByLevel(6-i) :item="props.item"/>
                                    </VRow>
                                    <VRow v-else>
                                        <VCol md="3" cols="6" class="font-weight-bold">
                                            <span>{{ levels['sales'+(6-i)+'_name'] }}/수수료율</span>
                                            <div style="font-size: 0.8em; font-weight: bold;" v-if="props.item['sales'+(6-i)+'_id'] && $vuetify.display.smAndDown">
                                                <template v-if="corp.pv_options.paid.fee_input_mode === false">
                                                    <span>{{ hintSalesSettleFee(props.item, 6-i) }}</span>
                                                    <br>
                                                </template>
                                                <span>
                                                    ({{ hintSalesSettleTaxTypeText(props.item, 6-i, all_sales[(6-i)]) }})
                                                    = {{ hintSalesSettleTotalFee(props.item, 6-i, all_sales[(6-i)]) }}%
                                                </span>
                                            </div>
                                        </VCol>
                                        <VCol md="5" cols="3">
                                            {{ sales[6-i].value.find(obj => obj.id === props.item['sales'+(6-i)+'_id'])?.sales_name }}
                                        </VCol>
                                        <VCol md="4" cols="3">
                                            <span>
                                                <VChip :color="StatusColorSetter().getSelectIdColor((6-i))">
                                                    {{ props.item['sales'+(6-i)+'_fee'] }} %
                                                </VChip>    
                                            </span>
                                            <div style="font-size: 0.8em; font-weight: bold;" v-if="props.item['sales'+(6-i)+'_id'] && $vuetify.display.smAndDown === false">
                                                <template v-if="corp.pv_options.paid.fee_input_mode === false">
                                                    <span>{{ hintSalesSettleFee(props.item, 6-i) }}</span>
                                                    <br>
                                                </template>
                                                <span>
                                                    ({{ hintSalesSettleTaxTypeText(props.item, 6-i, all_sales[(6-i)]) }})
                                                    = {{ hintSalesSettleTotalFee(props.item, 6-i, all_sales[(6-i)]) }}%
                                                </span>
                                            </div>
                                        </VCol>
                                    </VRow>
                                </VCol>
                            </template>
                        </template>
                        <VDivider/>
                        <VCol cols="12">
                            <VCardTitle>가맹점 수수료</VCardTitle>
                        </VCol>
                        <VCol cols="12">
                            <VRow v-if="isAbleModiy(props.item.id)">
                                <VCol cols="12" md="3">
                                    * 가맹점/유보금 수수료율
                                </VCol>
                                    <VCol cols="6" :md="props.item.id ? 3 : 4">
                                        <VTextField v-model="props.item.trx_fee" type="number" suffix="%"
                                            :rules="MerchandiseTrxFeeValidate" v-if="isAbleModiy(props.item.id)"/>
                                    </VCol>
                                    <VCol cols="6" :md="props.item.id ? 3 : 4">
                                        <VTextField v-model="props.item.hold_fee" type="number" suffix="%"
                                            :rules="[requiredValidatorV2(props.item.hold_fee, '가맹점 유보금')]" v-if="isAbleModiy(props.item.id)"  />
                                    </VCol>
                                    <FeeChangeBtn v-if="props.item.id && isAbleModiy(props.item.id)" :level=-1 :item="props.item"/>
                            </VRow>
                            <VRow v-else>
                                <VCol md="4" class="font-weight-bold" cols="6">가맹점/유보금 수수료율</VCol>
                                <VCol md="4" cols="3">
                                    <span>
                                        <VChip :color="StatusColorSetter().getSelectIdColor(0)">
                                            {{ props.item.trx_fee }} %
                                        </VChip>
                                    </span>
                                </VCol>
                                <VCol md="4" cols="3">
                                    <span>
                                        <VChip :color="StatusColorSetter().getSelectIdColor(0)">
                                            {{ props.item.hold_fee }} %
                                        </VChip>
                                    </span>
                                </VCol>
                            </VRow>
                        </VCol>
                        <VCol v-if="isAbleModiy(props.item.id)">
                            <VTextarea v-model="props.item.note" counter label="메모사항"
                                variant="filled"
                                prepend-inner-icon="twemoji-spiral-notepad" maxlength="300" auto-grow />
                        </VCol>
                    </VRow>
                </VCardItem>
            </VCard>
        </VCol>
        <VCol cols="12" md="6">
            <VCard>
                <VCardItem>
                    <VCardTitle>옵션정보</VCardTitle>
                    <VRow class="pt-5">
                        <VCol cols="12">
                            <VRow>
                                <VCol :md="6" :cols="12">
                                    <VRow no-gutters style="align-items: center;" v-if="isAbleModiy(props.item.id)">
                                        <VCol>사업자 유형</VCol>
                                        <VCol md="6">
                                            <div class="batch-container">
                                        <VAutocomplete :menu-props="{ maxHeight: 400 }" v-model="props.item.tax_category_type"
                                            :items="tax_category_types"
                                            prepend-inner-icon="ic-outline-business-center" label="사업자 종류" item-title="title"
                                            item-value="id" single-line/>
                                            </div>
                                        </VCol>
                                    </VRow>
                                    <VRow v-else>
                                        <VCol class="font-weight-bold">사업자 유형</VCol>
                                        <VCol md="6">
                                            <VChip :color="MerchantStatusColor(props.item.tax_category_type)">
                                                {{ tax_category_types.find(obj => obj.id === props.item.tax_category_type)?.title }}
                                            </VChip>
                                        </VCol>
                                    </VRow>
                                </VCol>
                                <VCol>
                                    <VRow no-gutters style="align-items: center;" v-if="isAbleModiy(props.item.id)">
                                        <VCol>커스텀 필터</VCol>
                                        <VCol md="6">
                                            <div class="batch-container">     
                                        <VAutocomplete :menu-props="{ maxHeight: 400 }" v-model="props.item.custom_id"
                                            :items="[{ id: null, type: 1, name: '사용안함' }].concat(cus_filters)"
                                            prepend-inner-icon="tabler:folder-question" label="커스텀 필터" item-title="name"
                                            item-value="id" single-line/>
                                            </div>
                                        </VCol>
                                    </VRow>
                                    <VRow v-else>
                                        <VCol class="font-weight-bold">커스텀 필터</VCol>
                                        <VCol md="6"><span>{{ cus_filters.find(obj => obj.id === props.item.custom_id)?.name }}</span></VCol>
                                    </VRow>
                                </VCol>
                            </VRow>
                        </VCol>
                        <VCol cols="12">
                            <VRow>
                                <VCol md="6">
                                    <VRow no-gutters style="align-items: center;" v-if="isAbleModiy(props.item.id)">
                                        <VCol>
                                            <BaseQuestionTooltip :location="'top'" :text="'가맹점 상태'" :content="'- 정상 : 거래 유지 중<br>- 해지 : 승인X, 취소X, 가맹점 관리자 접속O<br>- 중지 : 승인X, 취소X, 가맹점 관리자 접속X'"/>
                                        </VCol>
                                        <VCol md="6">
                                            <div class="batch-container">
                                                <VAutocomplete :menu-props="{ maxHeight: 400 }" v-model="props.item.merchant_status"
                                                    :items="merchant_statuses"
                                                    prepend-inner-icon="pajamas:status-health" label="가맹점 상태" item-title="title"
                                                    item-value="id" single-line/>
                                            </div>
                                        </VCol>
                                    </VRow>
                                    <VRow v-else>
                                        <VCol class="font-weight-bold">가맹점 상태</VCol>
                                        <VCol md="6">
                                            <VChip :color="MerchantStatusColor(props.item.merchant_status)">
                                                {{ merchant_statuses.find(obj => obj.id === props.item.merchant_status)?.title }}
                                            </VChip>
                                        </VCol>
                                    </VRow>
                                </VCol>
                                <VCol :md="6">
                                    <VRow no-gutters style="align-items: center;" v-if="isAbleModiy(props.item.id)">
                                        <VCol>지급이체 수수료</VCol>
                                        <VCol md="6">
                                            <div class="batch-container">
                                            <VTextField v-model="props.item.withdraw_fee" type="number" suffix="₩"
                                                :rules="[requiredValidatorV2(props.item.withdraw_fee, '지급이체 수수료')]" />
                                            </div>
                                        </VCol>
                                    </VRow>
                                    <VRow v-else>
                                        <VCol class="font-weight-bold">지급이체 수수료</VCol>
                                        <VCol md="6"><span>{{ props.item.withdraw_fee }}₩</span></VCol>
                                    </VRow>
                                </VCol>
                            </VRow>
                        </VCol>
                        <VCol cols="12" v-if="corp.pv_options.paid.use_regular_card || corp.pv_options.paid.use_multiple_hand_pay">
                            <VDivider style="margin-bottom: 1em;"/>
                            <VRow>
                                <VCol :md="6" :cols="12">
                                    <VRow no-gutters style="align-items: center;" v-if="isAbleModiy(props.item.id)">
                                        <VCol>단골고객 사용여부</VCol>
                                        <VCol md="6">
                                            <div class="batch-container">
                                                <BooleanRadio :radio="props.item.use_regular_card"
                                                    @update:radio="props.item.use_regular_card = $event">
                                                    <template #true>사용</template>
                                                    <template #false>미사용</template>
                                                </BooleanRadio>
                                            </div>
                                        </VCol>
                                    </VRow>
                                    <VRow v-else>
                                        <VCol class="font-weight-bold">단골고객 사용여부</VCol>
                                        <VCol md="6"><span>{{ props.item.use_regular_card ? "사용" : "미사용" }}</span></VCol>
                                    </VRow>
                                </VCol>
                                <VCol v-if="corp.pv_options.paid.use_multiple_hand_pay" md="6">
                                    <VRow no-gutters :md="6" style="align-items: center;">
                                        <VCol>다중 수기결제 사용 여부</VCol>
                                        <VCol md="6">
                                            <div class="batch-container">
                                                <BooleanRadio :radio="props.item.use_multiple_hand_pay"
                                                    @update:radio="props.item.use_multiple_hand_pay = $event">
                                                    <template #true>활성</template>
                                                    <template #false>비활성</template>
                                                </BooleanRadio>
                                            </div>
                                        </VCol>
                                    </VRow>
                                </VCol>
                            </VRow>
                        </VCol>
                        <template v-if="corp.pv_options.paid.use_collect_withdraw">
                            <VCol cols="12">
                                <VDivider style="margin-bottom: 1em;"/>
                                <VRow>
                                    <VCol :md="6" :cols="12">
                                        <VRow no-gutters style="align-items: center;" v-if="isAbleModiy(props.item.id)">
                                            <VCol>모아서 출금</VCol>
                                            <VCol md="6">
                                                <div class="batch-container">
                                                    <BooleanRadio :radio="props.item.use_collect_withdraw"
                                                        @update:radio="props.item.use_collect_withdraw = $event">
                                                        <template #true>사용</template>
                                                        <template #false>미사용</template>
                                                    </BooleanRadio>
                                                </div>
                                            </VCol>
                                        </VRow>
                                        <VRow v-else>
                                            <VCol class="font-weight-bold">모아서 출금</VCol>
                                            <VCol md="6"><span>{{ props.item.use_collect_withdraw ? "사용" : "미사용" }}</span></VCol>
                                        </VRow>
                                    </VCol>
                                    <VCol>
                                        <VRow no-gutters style="align-items: center;" v-if="isAbleModiy(props.item.id)">
                                            <VCol md="6" cols="7">
                                                <BaseQuestionTooltip :location="'top'" :text="'모아서 출금 수수료'"
                                                    :content="'모아서 출금 사용시마다 적용되는 수수료 입니다.'"/>
                                            </VCol>
                                            <VCol md="6">
                                                <div class="batch-container">     
                                                    <VTextField v-model="props.item.collect_withdraw_fee" type="number" suffix="₩"
                                                        :rules="[requiredValidatorV2(props.item.collect_withdraw_fee, '모아서 출금')]" />
                                                </div>
                                            </VCol>
                                        </VRow>
                                        <VRow v-else>
                                            <VCol class="font-weight-bold">모아서 출금 수수료</VCol>
                                            <VCol md="6"><span>{{ props.item.collect_withdraw_fee }} ₩</span></VCol>
                                        </VRow>
                                    </VCol>
                                </VRow>
                            </VCol>
                        </template>
                        <template v-if="isSalesAddTIDMode(props.item.id)">
                            <VCol cols="12">
                                <VDivider style="margin-bottom: 1em;"/>
                                <VRow>
                                    <VCol :md="6" :cols="12">
                                        <VRow no-gutters style="align-items: center;">
                                            <VCol>
                                                <VSwitch v-if="isAbleModiy(props.item.id)" 
                                                    hide-details :false-value=0 :true-value=1 
                                                    v-model="props.item.tid_auto_issue"
                                                    label="TID 추가" color="info"
                                                />
                                            </VCol>
                                            <VCol md="6">
                                                <VTextField type="text" v-model="props.item.tid" prepend-inner-icon="jam-key-f"
                                                    placeholder="TID 입력" persistent-placeholder
                                                    maxlength="50" :rules="props.item.tid_auto_issue ? [requiredValidatorV2(props.item.tid, 'TID')] : []"
                                                    :disabled="!props.item.tid_auto_issue"
                                                />
                                            </VCol>
                                        </VRow>
                                    </VCol>
                                </VRow>
                            </VCol>
                        </template>
                        <template v-if="getUserLevel() >= 35">
                            <VCol cols="12">
                                <VDivider style="margin-bottom: 1em;"/>
                                <VRow>
                                    <VCol :md="6" :cols="12" v-if="corp.pv_options.paid.use_noti">
                                        <VRow no-gutters style="align-items: center;">
                                            <VCol>노티 발송 여부</VCol>
                                            <VCol md="6">
                                                <div class="batch-container">
                                                    <BooleanRadio :radio="props.item.use_noti"
                                                        @update:radio="props.item.use_noti = $event">
                                                        <template #true>활성</template>
                                                        <template #false>비활성</template>
                                                    </BooleanRadio>
                                                </div>
                                            </VCol>
                                        </VRow>
                                    </VCol>
                                    <VCol :md="6" :cols="12">
                                        <VRow no-gutters style="align-items: center;">
                                            <VCol>매출전표 공급자 정보</VCol>
                                            <VCol md="6">
                                                <div class="batch-container">
                                                    <BooleanRadio :radio="props.item.use_saleslip_prov"
                                                        @update:radio="props.item.use_saleslip_prov = $event">
                                                        <template #true>PG사</template>
                                                        <template #false>운영사</template>
                                                    </BooleanRadio>
                                                </div>
                                            </VCol>
                                        </VRow>
                                    </VCol>
                                </VRow>
                            </VCol>
                            <VCol cols="12" v-if="getUserLevel() >= 35">
                                <VDivider style="margin-bottom: 1em;"/>
                                <VRow>
                                    <VCol :md="12" :cols="12">
                                        <VRow style="align-items: center;" class="match-height">
                                            <VCol md="6" cols=12>
                                                <VTextarea v-model="props.item.settle_hold_reason" counter label="지급보류 사유"
                                                    variant="filled"
                                                    prepend-inner-icon="twemoji-spiral-notepad" maxlength="200" auto-grow />
                                            </VCol>
                                            <VCol md="6" cols="12" style="display: flex;flex-direction: column;text-align: end;">
                                                <div>
                                                    <AppDateTimePicker
                                                        v-model="props.item.settle_hold_s_dt" 
                                                        prepend-inner-icon="ic-baseline-calendar-today"
                                                        placeholder="지급보류 시작일 입력"
                                                        label="지급보류 시작일"
                                                        style="max-width: 14em; margin-bottom: 3em; margin-left: auto;"
                                                    />
                                                </div>
                                                <div style="float: inline-end;">
                                                    <VBtn color="error" @click="setSettleHoldClear()" style='margin-bottom: 1em;'>
                                                        지급보류
                                                        <VIcon end icon="icon-park-solid:clear-format" />
                                                    </VBtn>
                                                    <VBtn color="error" variant="tonal" @click="clearSettleHoldClear()" style=" margin-bottom: 1em;margin-left: 1em;">
                                                        지급보류해제
                                                        <VIcon end icon="icon-park-solid:clear-format" />
                                                    </VBtn>
                                                </div>
                                            </VCol>
                                        </VRow>
                                    </VCol>
                                </VRow>
                                <VDivider/>
                            </VCol>
                            <VCol cols="12" v-if="corp.pv_options.paid.use_pay_verification_mobile && getUserLevel() >= 35">
                                <VRow>
                                    <VCol :md="6" :cols="12">
                                        <VCardTitle>결제창 SMS 인증</VCardTitle>       
                                    </VCol>                             
                                </VRow>
                                <VRow style="margin-bottom: 1em;">
                                    <VCol md="5" cols="12">
                                        <VRow no-gutters style="align-items: center;">
                                            <VCol md="7" cols="7">최대 인증허용 회수</VCol>
                                            <VCol md="5" cols="5">
                                                <VTextField v-model="props.item.phone_auth_limit_count" type="number" suffix="회 허용"
                                                    :rules="[requiredValidatorV2(props.item.phone_auth_limit_count, '최대 인증허용 회수')]" 
                                                    style="max-width: 120px; margin-right: 1em;"/>
                                                    <VTooltip activator="parent" location="top" transition="scale-transition">
                                                        <span>0 입력시 검증하지 않습니다.</span>
                                                    </VTooltip>
                                            </VCol>                                   
                                        </VRow>
                                    </VCol>
                                    <VCol :md="7" :cols="12">
                                        <div class="flex-container">
                                            <VTextField v-model="props.item.phone_auth_limit_s_tm" type="time" label="적용시작시간"
                                                style="max-width: 150px;"/>
                                            <span style="margin: 0 0.5em;">~</span>
                                            <VTextField v-model="props.item.phone_auth_limit_e_tm" type="time" label="적용종료시간"
                                                style="max-width: 150px;"/>
                                        </div>
                                    </VCol>
                                </VRow>
                                <VDivider />
                            </VCol>
                            <VCol cols="12" v-if="corp.pv_options.paid.use_specified_limit">
                                <SpecifiedTimeDisablePaymentCard :item="props.item"/>
                            </VCol>
                        </template>
                    </VRow>
                </VCardItem>
            </VCard>
            <template v-if="props.item.use_regular_card && isAbleModiy(props.item.id)">
                <br>
                <VCard>
                    <VCardItem>
                        <VCol cols="12">
                            <VRow>
                                <RegularCreditCard :item="props.item" />
                            </VRow>
                        </VCol>
                    </VCardItem>
                </VCard>
            </template>
            <template v-if="corp.pv_options.paid.use_product && isAbleModiy(props.item.id)">
                <br>
                <VCard>
                    <VCardItem>
                        <VCol cols="12">
                            <VRow>
                                <ProductCard :item="props.item"/>
                            </VRow>
                        </VCol>
                    </VCardItem>
                </VCard>
            </template>
        </VCol>
        <UnderAutoSettingDialog ref="underAutoSetting"/>
        <PasswordCheckDialog ref="passwordCheckDialog"/>
        <FeeBookDialog ref="feeBookDialog"/>
    </VRow>
</template>
<style scoped>
.flex-container {
  display: flex;
  align-items: center;
  justify-content: end;
}

</style>
