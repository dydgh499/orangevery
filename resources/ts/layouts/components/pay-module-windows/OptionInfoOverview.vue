<script lang="ts" setup>
import corp from '@/plugins/corp';
import { issuers } from '@/views/complaints/useStore';
import { cxl_types, installments, pay_limit_types, pay_window_extend_hours, pay_window_secure_levels } from '@/views/merchandises/pay-modules/useStore';
import { StatusColorSetter } from '@/views/searcher';
import type { PayModule } from '@/views/types';
import { isAbleModiyV2 } from '@axios';
import { requiredValidatorV2 } from '@validators';

interface Props {
    item: PayModule,
}
const props = defineProps<Props>()
</script>
<template>
    <VCardItem>
        <VCardSubtitle>
            <VChip variant="outlined">FDS 설정</VChip>
        </VCardSubtitle>
        <br>
        <VRow style="padding-top: 0.5em;">
            <template v-if="isAbleModiyV2(props.item, 'merchandises/pay-modules')">
                <VCol md="6" cols="12">
                    <VTextField type="number" v-model="props.item.pay_dupe_least"
                        prepend-inner-icon="tabler-currency-won" label="중복거래 하한금"
                        suffix="만원" />
                        <VTooltip activator="parent" location="top" transition="scale-transition">
                            설정 금액 이하로 결제가 발생하는 건은 중복거래 탐지에서 무시됩니다.
                        </VTooltip>
                </VCol>
                <VCol md="6">
                    <VTextField prepend-inner-icon="jam-triangle-danger" v-model="props.item.abnormal_trans_limit"
                        type="number" suffix="만원" label="이상거래 한도"
                        :rules="[requiredValidatorV2(props.item.abnormal_trans_limit, '이상거래 한도')]" />
                </VCol>
            </template>
            <template v-else>
                <VCol md="5" cols="6">
                    <span class="font-weight-bold">중복거래 하한금</span>
                </VCol>
                <VCol md="7" cols="6">
                    {{ props.item.pay_dupe_least }}만원
                </VCol>
                <VCol md="5" cols="6">
                    <span class="font-weight-bold">이상거래 한도</span>
                </VCol>
                <VCol md="7" cols="6">
                    {{ props.item.abnormal_trans_limit }}만원
                </VCol>
            </template>
        </VRow>
        <VRow>
            <template v-if="isAbleModiyV2(props.item, 'merchandises/pay-modules')">
                <VCol md="6">
                    <VSelect :menu-props="{ maxHeight: 400 }" v-model="props.item.installment" :items="installments"
                        prepend-inneer-icon="fluent-credit-card-clock-20-regular" label="할부한도" item-title="title"
                        item-value="id" />
                </VCol>
            </template>
            <template v-else>
                <VCol md="5" cols="6">
                    <span class="font-weight-bold">할부한도</span>
                </VCol>
                <VCol md="7" cols="6">
                    {{ installments.find(obj => obj.id === props.item.installment)?.title }}
                </VCol>
            </template>
        </VRow>
        
        <template v-if="props.item.module_type != 0">
            <VDivider style="margin: 1em 0;" />
            <VCardSubtitle>
                <VChip variant="outlined">결제/취소 제한</VChip>
            </VCardSubtitle>
            <br>            
            <VRow>
                <template v-if="isAbleModiyV2(props.item, 'merchandises/pay-modules')">
                    <VCol md="6" cols="12">
                        <VAutocomplete :menu-props="{ maxHeight: 400 }" v-model="props.item.cxl_type" :items="cxl_types"
                                label="취소타입" item-title="title" item-value="id" />
                    </VCol>
                    <VCol md="6" cols="12">
                        <VSelect :menu-props="{ maxHeight: 400 }" v-model="props.item.pay_limit_type" :items="pay_limit_types"
                            label="결제금지타입" item-title="title"
                            item-value="id" />
                    </VCol>
                </template>
                <template v-else>
                    <VCol md="5" cols="6">
                        <span class="font-weight-bold">취소타입</span>
                    </VCol>
                    <VCol md="7" cols="6">
                        <VChip :color="StatusColorSetter().getSelectIdColor(props.item.cxl_type)">
                            {{ cxl_types.find(obj => obj.id === props.item.cxl_type)?.title }}
                        </VChip>
                    </VCol>
                    <VCol md="5" cols="6">
                        <span class="font-weight-bold">결제금지타입</span>
                    </VCol>
                    <VCol md="7" cols="6">
                        <VChip :color="StatusColorSetter().getSelectIdColor(props.item.pay_limit_type)">
                            {{ pay_limit_types.find(obj => obj.id === props.item.pay_limit_type)?.title }}
                        </VChip>
                    </VCol>
                </template>
            </VRow>
            <VRow>
                <template v-if="isAbleModiyV2(props.item, 'merchandises/pay-modules')">
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
                </template>
                <template v-else>
                    <template v-if="props.item.pay_disable_s_tm || props.item.pay_disable_e_tm">
                        <VCol md="5" cols="6">
                            <span class="font-weight-bold">결제금지시간</span>
                        </VCol>
                        <VCol md="7" cols="6">
                            {{ props.item.pay_disable_s_tm }} ~ {{ props.item.pay_disable_e_tm }}
                        </VCol>
                    </template>
                </template>
            </VRow>
            <VRow>
                <template v-if="isAbleModiyV2(props.item, 'merchandises/pay-modules')">
                    <VCol md="6" cols="12"  v-if="corp.pv_options.paid.use_dup_pay_validation">
                        <VTextField v-model="props.item.pay_dupe_limit" type="number" suffix="회 허용" label="동일카드 결제"
                            :rules="[requiredValidatorV2(props.item.pay_dupe_limit, '동일카드 결제')]" />
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
                </template>
                <template v-else>
                    <template v-if="corp.pv_options.paid.use_dup_pay_validation && props.item.pay_dupe_limit">
                        <VCol md="5" cols="6">
                            <span class="font-weight-bold">동일카드 결제</span>
                        </VCol>
                        <VCol md="7" cols="6">
                            {{ props.item.pay_dupe_limit }}회 허용
                        </VCol>
                    </template>
                    <template v-if="props.item.payment_term_min">
                        <VCol md="5" cols="6">
                            <span class="font-weight-bold">결제 허용 간격</span>
                        </VCol>
                        <VCol md="7" cols="6">
                            {{ props.item.payment_term_min }}분
                        </VCol>
                    </template>
                </template>
            </VRow>
            <VRow v-if="corp.pv_options.paid.use_issuer_filter">
                <template v-if="isAbleModiyV2(props.item, 'merchandises/pay-modules')">
                    <VCol md="6" cols="12" >
                        <VAutocomplete :menu-props="{ maxHeight: 400 }" v-model="props.item.filter_issuers"
                            label="발급사 필터링" :items="issuers" item-title="title"
                            item-value="code" multiple chips closable-chips />
                            <VTooltip activator="parent" location="top" transition="scale-transition">
                                해당 발급사로 결제된 카드는 강제취소됩니다.
                            </VTooltip>
                    </VCol>
                </template>
                <template v-else>
                    <VCol md="5" cols="6">
                        <span class="font-weight-bold">발급사 필터링</span>
                    </VCol>
                    <VCol md="7" cols="6">
                        <VChip v-for="(issuer) in props.item.filter_issuers">
                            {{ issuer }}
                        </VChip>
                    </VCol>
                </template>
            </VRow>
            <VDivider style="margin: 1em 0;" />
            <VCardSubtitle>
                <VChip variant="outlined">결제한도</VChip>
            </VCardSubtitle>
            <br>
            <VRow>                
                <template v-if="isAbleModiyV2(props.item, 'merchandises/pay-modules')">
                    <VCol md="6" cols="12">
                        <VTextField prepend-inner-icon="tabler-currency-won" v-model="props.item.pay_single_limit"
                            type="number" suffix="만원" label="단건 결제한도"
                            :rules="[requiredValidatorV2(props.item.pay_single_limit, '단건 결제한도')]" />
                    </VCol>
                    <VCol md="6">
                        <VTextField prepend-inner-icon="tabler-currency-won" v-model="props.item.pay_day_limit"
                            type="number" suffix="만원" label="일 결제한도"
                            :rules="[requiredValidatorV2(props.item.pay_day_limit, '일 결제한도')]" />
                    </VCol>
                </template>
                <template v-else>
                    <VCol md="5" cols="6">
                        <span class="font-weight-bold">단건 결제한도</span>
                    </VCol>
                    <VCol md="7" cols="6">
                        {{ props.item.pay_single_limit }}만원
                    </VCol>
                    <VCol md="5" cols="6">
                        <span class="font-weight-bold">일 결제한도</span>
                    </VCol>
                    <VCol md="7" cols="6">
                        {{ props.item.pay_day_limit }}만원
                    </VCol>
                </template>
            </VRow>
            <VRow>
                <template v-if="isAbleModiyV2(props.item, 'merchandises/pay-modules')">
                    <VCol md="6" cols="12">
                        <VTextField prepend-inner-icon="tabler-currency-won" v-model="props.item.pay_month_limit"
                            type="number" suffix="만원" label="월 결제한도"
                            :rules="[requiredValidatorV2(props.item.pay_month_limit, '월 결제한도')]" />
                    </VCol>
                    <VCol md="6">
                        <VTextField prepend-inner-icon="tabler-currency-won" v-model="props.item.pay_year_limit"
                            type="number" suffix="만원" label="연 결제한도"
                            :rules="[requiredValidatorV2(props.item.pay_year_limit, '연 결제한도')]" />
                    </VCol>
                </template>
                <template v-else>
                    <VCol md="5" cols="6">
                        <span class="font-weight-bold">월 결제한도</span>
                    </VCol>
                    <VCol md="7" cols="6">
                        {{ props.item.pay_month_limit }}만원
                    </VCol>
                    <VCol md="5" cols="6">
                        <span class="font-weight-bold">연 결제한도</span>
                    </VCol>
                    <VCol md="7" cols="6">
                        {{ props.item.pay_year_limit }}만원
                    </VCol>
                </template>
            </VRow>
            <VDivider style="margin: 1em 0;" />
            <VCardSubtitle>
                <VChip variant="outlined">결제창</VChip>                
            </VCardSubtitle>
            <br>
            <VRow>
                <template v-if="isAbleModiyV2(props.item, 'merchandises/pay-modules')">
                    <VCol md="6" cols="12">
                        <VSelect v-model="props.item.pay_window_secure_level" :items="pay_window_secure_levels" prepend-inner-icon="tabler:shield-lock"
                                item-title="title" item-value="id" label="결제창 보안등급"/>
                    </VCol>
                    <VCol md="6">
                        <VSelect v-model="props.item.pay_window_extend_hour" :items="pay_window_extend_hours"
                            prepend-inner-icon="tabler:clock-plus" item-title="title" item-value="id" label="결제창 연장시간"
                            />
                    </VCol>
                </template>
                <template v-else>
                    <VCol md="5" cols="6">
                        <span class="font-weight-bold">결제창 보안등급</span>
                    </VCol>
                    <VCol md="7" cols="6">
                        <VChip :color="StatusColorSetter().getSelectIdColor(props.item.pay_window_secure_level)">
                            {{ pay_window_secure_levels.find(obj => obj.id === props.item.pay_window_secure_level)?.title }}
                        </VChip>
                    </VCol>
                    <VCol md="5" cols="6">
                        <span class="font-weight-bold">결제창 연장시간</span>
                    </VCol>
                    <VCol md="7" cols="6">
                        {{ pay_window_extend_hours.find(obj => obj.id === props.item.pay_window_extend_hour)?.title }}
                    </VCol>
                </template>
            </VRow>
        </template>
    </VCardItem>
    <slot name="edit"></slot>
</template>
<style scoped>
:deep(.v-row) {
  align-items: center;
}
</style>
