<script lang="ts" setup>
import { requiredValidator } from '@validators'
import type { Merchandise, UnderAutoSetting } from '@/views/types'
import BooleanRadio from '@/layouts/utils/BooleanRadio.vue'
import PasswordCheckDialog from '@/layouts/dialogs/users/PasswordCheckDialog.vue'
import FeeBookDialog from '@/layouts/dialogs/users/FeeBookDialog.vue'

import { getUserLevel, getIndexByLevel } from '@axios'
import { useRequestStore } from '@/views/request'
import { useSalesFilterStore } from '@/views/salesforces/useStore'
import FeeChangeBtn from '@/views/merchandises/FeeChangeBtn.vue'
import { useStore } from '@/views/services/pay-gateways/useStore'
import UnderAutoSettingDialog from '@/layouts/dialogs/users/UnderAutoSettingDialog.vue'
import RegularCreditCard from '@/views/merchandises/regular-credit-cards/RegularCreditCard.vue'
import BaseQuestionTooltip from '@/layouts/tooltips/BaseQuestionTooltip.vue'
import { tax_category_types } from '@/views/merchandises/useStore'
import corp from '@corp'

interface Props {
    item: Merchandise,
}

const props = defineProps<Props>()
const { post } = useRequestStore()
const { sales, initAllSales, sales_apply_histories, hintSalesApplyFee } = useSalesFilterStore()
const { cus_filters } = useStore()

const alert = <any>(inject('alert'))
const levels = corp.pv_options.auth.levels

const feeBookDialog = ref()
const underAutoSetting = ref()
const passwordCheckDialog = ref()

provide('feeBookDialog', feeBookDialog)

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

initAllSales()
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
                                    <VRow no-gutters style="align-items: center;">
                                        <VCol>가맹점 상호</VCol>
                                        <VCol md="8">
                                            <VTextField v-model="props.item.mcht_name" prepend-inner-icon="tabler-building-store"
                                            placeholder="상호를 입력해주세요" persistent-placeholder :rules="[requiredValidator]" />
                                        </VCol>
                                    </VRow>
                                </VCol>
                                <VCol cols="12" md="6">
                                    <VRow no-gutters style="align-items: center;">
                                        <VCol>업종</VCol>
                                        <VCol md="8">
                                            <VTextField v-model="props.item.sector" prepend-inner-icon="tabler-building-store"
                                                placeholder="업종을 입력해주세요" persistent-placeholder />
                                        </VCol>
                                    </VRow>
                                </VCol>
                            </VRow>
                        </VCol>
                        <VCol cols="12" v-if="corp.use_different_settlement">
                            <VRow>
                                <VCol cols="12" md="6">
                                    <VRow no-gutters style="align-items: center;">
                                        <VCol>이메일</VCol>
                                        <VCol md="8"> 
                                            <VTextField v-model="props.item.email" prepend-inner-icon="material-symbols:mail"
                                                placeholder="이메일을 입력해주세요" persistent-placeholder>
                                                <VTooltip activator="parent" location="top" maxlength="50">
                                                    하위몰이 대표 이메일주소
                                                </VTooltip>
                                            </VTextField>
                                        </VCol>
                                    </VRow>
                                </VCol>
                                <VCol cols="12" md="6">
                                    <VRow no-gutters style="align-items: center;">
                                        <VCol>웹사이트 URL</VCol>
                                        <VCol md="8">
                                            <VTextField v-model="props.item.website_url" prepend-inner-icon="streamline:browser-website-1-solid"
                                                placeholder="웹사이트 URL 입력해주세요" persistent-placeholder maxlength="250">
                                                <VTooltip activator="parent" location="top">
                                                    하위몰이 없는경우 2차PG사 URL을 입력해주세요.
                                                </VTooltip>
                                            </VTextField>
                                        </VCol>
                                    </VRow>
                                </VCol>
                            </VRow>
                        </VCol>
                        <!-- 👉 상위 영업점 수수료율 -->
                        <template v-for="i in 6" :key="i">
                            <VCol cols="12" v-if="levels['sales'+(6-i)+'_use'] && getUserLevel() >= getIndexByLevel(6-i)">
                                <VRow>
                                    <VCol cols="12" md="3">{{ levels['sales'+(6-i)+'_name'] }}/수수료율</VCol>
                                    <VCol cols="12" :md="props.item.id ? 3 : 4">
                                        <VAutocomplete :menu-props="{ maxHeight: 400 }" v-model="props.item['sales'+(6-i)+'_id']"
                                            :items="sales[6-i].value"
                                             :label="levels['sales'+(6-i)+'_name'] + '선택'"
                                            item-title="sales_name" item-value="id" persistent-hint single-line prepend-inner-icon="ph:share-network"
                                            :hint="hintSalesApplyFee(props.item['sales'+(6-i)+'_id'])" @update:modelValue="setSalesUnderAutoSetting(6-i)"/>

                                            <VTooltip activator="parent" location="top" v-if="props.item['sales'+(6-i)+'_id']">
                                                {{ sales[6-i].value.find(obj => obj.id === props.item['sales'+(6-i)+'_id'])?.sales_name }}
                                            </VTooltip>
                                    </VCol>
                                    <VCol cols="12" :md="props.item.id ? 2 : 4">
                                        <VTextField v-model="props.item['sales'+(6-i)+'_fee'] " type="number" suffix="%"
                                            :rules="[requiredValidator]" />
                                    </VCol>
                                    <FeeChangeBtn v-if="props.item.id" :level=getIndexByLevel(6-i) :item="props.item">
                                    </FeeChangeBtn>
                                </VRow>
                            </VCol>
                        </template>
                        <!-- 👉 가맹점 수수료율 -->
                        <VCol cols="12">
                            <VRow>
                                <VCol cols="12" md="3">
                                    거래/유보금 수수료율
                                </VCol>
                                <VCol cols="12" :md="props.item.id ? 3 : 4">
                                    <VTextField v-model="props.item.trx_fee" type="number" suffix="%"
                                        :rules="[requiredValidator]" />
                                </VCol>
                                <VCol cols="12" :md="props.item.id ? 2 : 4">
                                    <VTextField v-model="props.item.hold_fee" type="number" suffix="%"
                                        :rules="[requiredValidator]" />
                                </VCol>
                                <FeeChangeBtn v-if="props.item.id" :level=-1 :item="props.item">
                                </FeeChangeBtn>
                            </VRow>
                        </VCol>
                        <VCol>
                            <VTextarea v-model="props.item.note" counter label="메모사항"
                                prepend-inner-icon="twemoji-spiral-notepad" maxlength="300" auto-grow />
                        </VCol>
                    </VRow>
                </VCardItem>
            </VCard>
        </VCol>
        <!-- 👉 계약정보 -->
        <VCol cols="12" md="6">
            <VCard>
                <VCardItem>
                    <VCardTitle>옵션정보</VCardTitle>
                    <VRow class="pt-5">
                        <VCol cols="12">
                            <VRow>
                                <VCol :md="6" :cols="12">
                                    <VRow no-gutters style="align-items: center;">
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
                                </VCol>
                                <VCol>
                                    <VRow no-gutters style="align-items: center;">
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
                                </VCol>
                            </VRow>
                        </VCol>
                        <VCol cols="12" v-if="corp.pv_options.paid.use_regular_card || corp.pv_options.paid.use_withdraw_fee">
                            <VRow>
                                <!-- 👉 단골고객 사용여부 -->
                                <VCol :md="6" :cols="12" v-if="corp.pv_options.paid.use_regular_card">
                                    <VRow no-gutters style="align-items: center;">
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
                                </VCol>
                                <VCol :md="6" v-if="corp.pv_options.paid.use_withdraw_fee">
                                    <VRow no-gutters style="align-items: center;">
                                        <VCol>출금 수수료</VCol>
                                        <VCol md="6">
                                            <div class="batch-container">
                                        <VTextField v-model="props.item.withdraw_fee" type="number" suffix="₩"
                                            :rules="[requiredValidator]" />
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
                                        <VRow no-gutters style="align-items: center;">
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
                                    </VCol>
                                    <VCol>
                                        <VRow no-gutters style="align-items: center;">
                                            <VCol>
                                            <BaseQuestionTooltip :location="'top'" :text="'모아서 출금 수수료'"
                                                :content="'모아서 출금 사용시마다 적용되는 수수료 입니다.'">
                                            </BaseQuestionTooltip>
                                        </VCol>
                                            <VCol md="6">
                                                <div class="batch-container">     
                                            <VTextField v-model="props.item.collect_withdraw_fee" type="number" suffix="₩"
                                                :rules="[requiredValidator]" />
                                                </div>
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
                                    <VCol :md="6" :cols="12">
                                        <VRow no-gutters style="align-items: center;">
                                            <VCol>매출전표 공급자 정보</VCol>
                                            <VCol md="6">
                                                <div class="batch-container">
                                            <BooleanRadio :radio="props.item.use_saleslip_prov"
                                                @update:radio="props.item.use_saleslip_prov = $event">
                                                <template #true>PG사</template>
                                                <template #false>본사</template>
                                            </BooleanRadio>
                                                </div>
                                            </VCol>
                                        </VRow>
                                    </VCol>
                                    <VCol>
                                        <VRow no-gutters style="align-items: center;">
                                            <VCol>매출전표 판매자 정보</VCol>
                                            <VCol md="6">
                                                <div class="batch-container">     
                                                    <BooleanRadio :radio="props.item.use_saleslip_sell"
                                                    @update:radio="props.item.use_saleslip_sell = $event">
                                                    <template #true>본사</template>
                                                    <template #false>가맹점</template>
                                                </BooleanRadio>
                                                </div>
                                            </VCol>
                                        </VRow>
                                    </VCol>
                                </VRow>
                                <VDivider style="margin: 1em 0;"/>
                            </VCol>
                            <VCol cols="12" v-if="corp.pv_options.paid.subsidiary_use_control || corp.pv_options.paid.use_hide_account">
                                <VRow>
                                    <VCol :md="6" :cols="12" v-if="corp.pv_options.paid.subsidiary_use_control">
                                        <VRow no-gutters style="align-items: center;">
                                            <VCol>전산 사용상태</VCol>
                                            <VCol md="6">
                                                <div class="batch-container">
                                                    <BooleanRadio :radio="props.item.enabled"
                                                        @update:radio="props.item.enabled = $event">
                                                        <template #true>ON</template>
                                                        <template #false>OFF</template>
                                                    </BooleanRadio>
                                                </div>
                                            </VCol>
                                        </VRow>
                                    </VCol>
                                    <VCol v-if="corp.pv_options.paid.use_hide_account">
                                        <VRow no-gutters style="align-items: center;">
                                            <VCol>정산계좌 숨김</VCol>
                                            <VCol md="6">
                                                <div class="batch-container">     
                                                    <BooleanRadio :radio="props.item.is_hide_account"
                                                        @update:radio="props.item.is_hide_account = $event">
                                                        <template #true>숨김</template>
                                                        <template #false>노출</template>
                                                    </BooleanRadio>
                                                </div>
                                            </VCol>
                                        </VRow>
                                    </VCol>
                                </VRow>
                            </VCol>
                            <VCol cols="12">
                                <VRow>
                                    <!-- 👉 노티 발송 여부 -->
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
                                    <VCol :md="6">
                                        <VRow no-gutters style="align-items: center;">
                                            <VCol>가맹점 수수료율 노출</VCol>
                                            <VCol md="6">
                                                <div class="batch-container">
                                            <BooleanRadio :radio="props.item.is_show_fee"
                                                @update:radio="props.item.is_show_fee = $event">
                                                <template #true>노출</template>
                                                <template #false>숨김</template>
                                            </BooleanRadio>
                                                </div>
                                            </VCol>
                                        </VRow>
                                    </VCol>
                                </VRow>
                            </VCol>
                            <VCol cols="12" v-if="corp.pv_options.paid.use_pay_verification_mobile || corp.pv_options.paid.use_multiple_hand_pay">
                                <VRow>
                                    <VCol :md="6" :cols="12" v-if="corp.pv_options.paid.use_pay_verification_mobile">
                                        <VRow no-gutters style="align-items: center;">
                                            <VCol>결제전 휴대폰 인증</VCol>
                                            <VCol md="6">
                                                <div class="batch-container">
                                                    <BooleanRadio :radio="props.item.use_pay_verification_mobile"
                                                        @update:radio="props.item.use_pay_verification_mobile = $event">
                                                        <template #true>활성</template>
                                                        <template #false>비활성</template>
                                                    </BooleanRadio>
                                                </div>
                                            </VCol>
                                        </VRow>
                                    </VCol>
                                    <VCol v-if="corp.pv_options.paid.use_multiple_hand_pay">
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
                            <VCol cols="12" v-if="corp.pv_options.paid.use_settle_hold">
                                <VDivider style="margin-bottom: 1em;"/>
                                <VRow>
                                    <VCol :md="6" :cols="12">
                                        <VRow no-gutters style="align-items: center;">
                                            <VCol>지급보류 시작일</VCol>
                                            <VCol md="6">
                                                <div class="batch-container">                                                    
                                                    <VTextField type="date" v-model="props.item.settle_hold_s_dt"
                                                        prepend-inner-icon="ic-baseline-calendar-today" label="시작일 입력" single-line />
                                                </div>
                                            </VCol>
                                        </VRow>
                                    </VCol>
                                    <VCol>
                                        <VRow no-gutters style="align-items: center;">
                                            <VCol md="12">
                                                <div class="batch-container">     
                                                    <VTextarea v-model="props.item.settle_hold_reason" counter label="지급보류 사유"
                                                        prepend-inner-icon="twemoji-spiral-notepad" maxlength="200" auto-grow />
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
                                <VDivider style="margin-bottom: 1em;"/>
                            </VCol>
                        </template>
                    </VRow>
                </VCardItem>
            </VCard>
            <br>
            <VCard v-if="props.item.use_regular_card">
                <VCardItem>
                    <VCol cols="12">
                        <VRow>
                            <RegularCreditCard :item="props.item" />
                        </VRow>
                    </VCol>
                </VCardItem>
            </VCard>
        </VCol>
        <UnderAutoSettingDialog ref="underAutoSetting"/>
        <PasswordCheckDialog ref="passwordCheckDialog"/>
        <FeeBookDialog ref="feeBookDialog"/>
    </VRow>
</template>
