<script lang="ts" setup>
import corp from '@/plugins/corp';
import { issuers } from '@/views/complaints/useStore';
import { abnormal_trans_limits, cxl_types, installments, pay_limit_types, pay_window_extend_hours, pay_window_secure_levels } from '@/views/merchandises/pay-modules/useStore';
import type { PayModule } from '@/views/types';
import { isAbleModiy } from '@axios';
import { requiredValidatorV2 } from '@validators';

interface Props {
    item: PayModule,
}
const props = defineProps<Props>()
</script>
<template>
    <VCardItem>
        <VCardSubtitle>FDS 설정</VCardSubtitle>
        <br>
        <VRow style="padding-top: 0.5em;">
            <VCol md="6" cols="12">
                <VTextField type="number" v-model="props.item.pay_dupe_least"
                    prepend-inner-icon="tabler-currency-won" label="중복거래 하한금"
                    suffix="만원" />
                    <VTooltip activator="parent" location="top" transition="scale-transition">
                        설정 금액 이하로 결제가 발생하는 건은 중복거래 탐지에서 무시됩니다.
                    </VTooltip>
            </VCol>
            <VCol md="6">
                <VAutocomplete :menu-props="{ maxHeight: 400 }" v-model="props.item.abnormal_trans_limit" :items="abnormal_trans_limits"
                    prepend-inner-icon="jam-triangle-danger" label="이상거래 한도" item-title="title" item-value="id"
                />
            </VCol>
        </VRow>
        <VRow>
            <VCol md="6">
                <VSelect :menu-props="{ maxHeight: 400 }" v-model="props.item.installment" :items="installments"
                    prepend-inneer-icon="fluent-credit-card-clock-20-regular" label="할부한도" item-title="title"
                    item-value="id" />
            </VCol>
        </VRow>
        
        <template v-if="props.item.module_type != 0">
            <VDivider style="margin: 1em 0;" />
            <VCardSubtitle>결제/취소 제한</VCardSubtitle>
            <br>            
            <VRow v-if="isAbleModiy(props.item.id)">
                <VCol md="6" cols="12">
                    <VAutocomplete :menu-props="{ maxHeight: 400 }" v-model="props.item.cxl_type" :items="cxl_types"
                            label="취소타입" item-title="title" item-value="id" />
                </VCol>
                <VCol md="6" cols="12">
                    <VSelect :menu-props="{ maxHeight: 400 }" v-model="props.item.pay_limit_type" :items="pay_limit_types"
                        label="결제금지타입" item-title="title"
                        item-value="id" />
                </VCol>
            </VRow>
            <VRow v-else>
                <VCol md="5" cols="6">
                    <span class="font-weight-bold">취소타입</span>
                </VCol>
                <VCol md="7" cols="6">
                    {{ cxl_types.find(obj => obj.id === props.item.cxl_type)?.title }}
                </VCol>
            </VRow>

            <VRow v-if="corp.pv_options.paid.use_forb_pay_time">
                <VCol md="6" cols="12">
                    <AppDateTimePicker 
                        v-model="props.item.pay_disable_s_tm" label="결제금지 시작시간"
                        :config="{ mode: 'range', enableTime: true, noCalendar: true, dateFormat: 'H:i'  }"   
                        style="min-width: 10em;"
                    />
                </VCol>
                <VCol md="6" cols="12">
                    <AppDateTimePicker 
                            v-model="props.item.pay_disable_e_tm" label="결제금지 종료시간"
                            :config="{ mode: 'range', enableTime: true, noCalendar: true, dateFormat: 'H:i'  }"
                            style="min-width: 10em;"
                        />
                </VCol>
                <VTooltip activator="parent" location="top" transition="scale-transition">
                    해당 시간대에는 <b>온라인 결제</b>를 발생시킬 수 없습니다.
                </VTooltip>
            </VRow>
            <VRow>
                <VCol md="6" cols="12"  v-if="corp.pv_options.paid.use_dup_pay_validation">
                    <VTextField v-model="props.item.pay_dupe_limit" type="number" suffix="회 허용" label="동일카드 결제허용 회수"
                        :rules="[requiredValidatorV2(props.item.pay_dupe_limit, '동일카드 결제허용 회수')]" />
                    <VTooltip activator="parent" location="top" transition="scale-transition">
                        결제가 발생할 시 카드당 하루에 결제가 가능한 회수를 제한합니다.<br>0 입력 시 허용회수를 검증하지 않으며, <b>온라인 결제</b>만 적용 가능합니다.
                    </VTooltip>
                </VCol>
                <VCol md="6" cols="12">
                    <VTextField v-model="props.item.payment_term_min"
                        type="number" suffix="분" label="결제 허용 간격"
                        :rules="[requiredValidatorV2(props.item.payment_term_min, '결제 허용 간격')]" />
                    <VTooltip activator="parent" location="top" transition="scale-transition">
                        중복결제 방지를 위해 결제 텀을 설정합니다.<br>동일 결제모듈 + 동일금액 + 동일카드번호 조건일 시 동작합니다.
                    </VTooltip>
                </VCol>
            </VRow>
            <VRow v-if="corp.pv_options.paid.use_issuer_filter">
                <VCol md="6" cols="12" >
                    <VAutocomplete :menu-props="{ maxHeight: 400 }" v-model="props.item.filter_issuers"
                        label="발급사 필터링" :items="issuers" item-title="title"
                        item-value="code" multiple chips closable-chips />
                        <VTooltip activator="parent" location="top" transition="scale-transition">
                            해당 발급사로 결제된 카드는 강제취소됩니다.
                        </VTooltip>
                </VCol>
            </VRow>
            <template v-if="corp.pv_options.paid.use_pay_limit">
                <VDivider style="margin: 1em 0;" />
                <VCardSubtitle>결제 한도</VCardSubtitle>
                <br>
                <VRow>
                    <VCol md="6" cols="12">
                        <VTextField prepend-inner-icon="tabler-currency-won" v-model="props.item.pay_single_limit"
                            type="number" suffix="만원" label="단건 결제 한도"
                            :rules="[requiredValidatorV2(props.item.pay_single_limit, '단건 결제 한도')]" />
                    </VCol>
                    <VCol md="6">
                        <VTextField prepend-inner-icon="tabler-currency-won" v-model="props.item.pay_day_limit"
                            type="number" suffix="만원" label="일 결제 한도"
                            :rules="[requiredValidatorV2(props.item.pay_day_limit, '일 결제 한도')]" />
                    </VCol>
                </VRow>
                <VRow>
                    <VCol md="6" cols="12">
                        <VTextField prepend-inner-icon="tabler-currency-won" v-model="props.item.pay_month_limit"
                            type="number" suffix="만원" label="월 결제 한도"
                            :rules="[requiredValidatorV2(props.item.pay_month_limit, '월 결제 한도')]" />
                    </VCol>
                    <VCol md="6">
                        <VTextField prepend-inner-icon="tabler-currency-won" v-model="props.item.pay_year_limit"
                            type="number" suffix="만원" label="연 결제 한도"
                            :rules="[requiredValidatorV2(props.item.pay_year_limit, '연 결제 한도')]" />
                    </VCol>
                </VRow>
            </template>
            <VDivider style="margin: 1em 0;" />
            <VCardSubtitle>결제창 보안설정</VCardSubtitle>
            <br>
            <VRow>
                <VCol md="6" cols="12">
                    <VSelect v-model="props.item.pay_window_secure_level" :items="pay_window_secure_levels" prepend-inner-icon="tabler:shield-lock"
                            item-title="title" item-value="id" label="결제창 보안등급"/>
                </VCol>
                <VCol md="6">
                    <VSelect v-model="props.item.pay_window_extend_hour" :items="pay_window_extend_hours"
                        prepend-inner-icon="tabler:clock-plus" item-title="title" item-value="id" label="결제창 연장시간"
                        />
                </VCol>
            </VRow>
        </template>
    </VCardItem>
</template>
<style scoped>
:deep(.v-row) {
  align-items: center;
}
</style>
