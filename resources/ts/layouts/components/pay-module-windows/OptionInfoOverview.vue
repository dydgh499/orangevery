<script lang="ts" setup>
import BaseQuestionTooltip from '@/layouts/tooltips/BaseQuestionTooltip.vue'
import BooleanRadio from '@/layouts/utils/BooleanRadio.vue'
import { issuers } from '@/views/complaints/useStore'
import { abnormal_trans_limits, cxl_types } from '@/views/merchandises/pay-modules/useStore'
import type { PayModule } from '@/views/types'
import corp from '@corp'
import { requiredValidatorV2 } from '@validators'

interface Props {
    item: PayModule,
}
const props = defineProps<Props>()

</script>
<template>
    <VCardItem>
        <VRow>
            <VCol md="5" cols="4">취소타입</VCol>
            <VCol md="7">
                <VSelect v-model="props.item.cxl_type" :items="cxl_types" prepend-inner-icon="tabler:world-cancel"
                        label="취소타입 설정" item-title="title" item-value="id" single-line />
            </VCol>
        </VRow>
        <VRow>
            <VCol md="6" cols="7">
                <BaseQuestionTooltip :location="'top'" :text="'이상거래 한도설정'"
                    :content="'설정 금액 이상으로 결제가 발생할 시, 이상거래 관리 목록에 추가됩니다.'"/>
            </VCol>
            <VCol md="6" cols="5">
                <VSelect v-model="props.item.abnormal_trans_limit" :items="abnormal_trans_limits"
                    prepend-inner-icon="jam-triangle-danger" label="이상거래 한도설정" item-title="title" item-value="id"
                    single-line />
            </VCol>
        </VRow>
        <VRow>
            <VCol md="6" cols="7">
                <BaseQuestionTooltip :location="'top'" :text="'중복거래 하한금'"
                    :content="'설정 금액 이하로 결제가 발생하는 건은 중복거래 탐지에서 무시됩니다.'"/>
            </VCol>
            <VCol md="6" cols="5">
                <VTextField type="number" v-model="props.item.pay_dupe_least"
                    prepend-inner-icon="tabler-currency-won" placeholder="이상거래 상한금 입력" persistent-placeholder
                    suffix="만원" />
            </VCol>
        </VRow>
        <VRow v-if="corp.pv_options.paid.use_dup_pay_validation && props.item.module_type != 0">
            <VCol md="6" cols="7">
                <BaseQuestionTooltip :location="'top'" :text="'동일카드 결제허용'"
                    :content="'결제가 발생할 시 카드당 하루에 결제가 가능한 회수를 제한합니다.<br>0 입력 시 허용회수를 검증하지 않으며, <b>온라인 결제</b>만 적용 가능합니다.'"/>
            </VCol>
            <VCol md="6" cols="5">
                <VTextField v-model="props.item.pay_dupe_limit" type="number" suffix="회 허용"
                    :rules="[requiredValidatorV2(props.item.pay_dupe_limit, '동일카드 결제허용 회수')]" />
            </VCol>
        </VRow>
        <template v-if="corp.pv_options.paid.use_pay_limit && props.item.module_type != 0">
            <VRow>
                <VCol md="6" cols="6">
                    <BaseQuestionTooltip :location="'top'" :text="'단건 결제 한도'"
                        :content="'결제 한도 금액: 1,000,000원 = 100 입력(이하동일)<br><b>온라인 결제</b>만 적용 가능합니다.'"/>
                </VCol>
                <VCol md="6">
                    <VTextField prepend-inner-icon="tabler-currency-won" v-model="props.item.pay_single_limit"
                        type="number" suffix="만원"
                        :rules="[requiredValidatorV2(props.item.pay_single_limit, '단건 결제 한도')]" />
                </VCol>
            </VRow>
            <VRow>
                <VCol md="6" cols="4">
                    일 결제 한도
                </VCol>
                <VCol md="6">
                    <VTextField prepend-inner-icon="tabler-currency-won" v-model="props.item.pay_day_limit"
                        type="number" suffix="만원"
                        :rules="[requiredValidatorV2(props.item.pay_day_limit, '일 결제 한도')]" />
                </VCol>
            </VRow>
            <VRow>
                <VCol md="6" cols="4">
                    월 결제 한도
                </VCol>
                <VCol md="6">
                    <VTextField prepend-inner-icon="tabler-currency-won" v-model="props.item.pay_month_limit"
                        type="number" suffix="만원"
                        :rules="[requiredValidatorV2(props.item.pay_month_limit, '월 결제 한도')]" />
                </VCol>
            </VRow>
            <VRow>
                <VCol md="6" cols="4">
                    연 결제 한도
                </VCol>
                <VCol md="6">
                    <VTextField prepend-inner-icon="tabler-currency-won" v-model="props.item.pay_year_limit"
                        type="number" suffix="만원"
                        :rules="[requiredValidatorV2(props.item.pay_year_limit, '연 결제 한도')]" />
                </VCol>
            </VRow>
        </template>
        <VRow v-if="corp.pv_options.paid.use_forb_pay_time && props.item.module_type != 0">
            <VCol md="7" cols="7">
                <BaseQuestionTooltip :location="'top'" :text="'결제금지 시간'"
                    :content="'해당 시간대에는 <b>온라인 결제</b>를 발생시킬 수 없습니다.'"/>
            </VCol>
            <VCol md="5">
                <div class="d-flex flex-column">
                    <VTextField v-model="props.item.pay_disable_s_tm" type="time" label="시작시간" style="width: 9em; margin-bottom: 1em;"/>
                    <VTextField v-model="props.item.pay_disable_e_tm" type="time" label="종료시간" style="width: 9em;"/>
                </div>
            </VCol>
        </VRow>
        <VRow v-if="corp.pv_options.paid.use_issuer_filter && props.item.module_type != 0">
            <VCol md="5" cols="4">
                <BaseQuestionTooltip :location="'top'" :text="'발급사 필터링'" :content="'해당 발급사로 결제된 카드는 강제취소됩니다.'"/>
            </VCol>
            <VCol md="7">
                <VAutocomplete :menu-props="{ maxHeight: 400 }" v-model="props.item.filter_issuers"
                    label="필터링할 발급사 선택" :items="issuers" prepend-inner-icon="ph-buildings" item-title="title"
                    item-value="code" multiple chips closable-chips single-line />
            </VCol>
        </VRow>
        <VRow v-if="props.item.module_type != 0">
            <VCol md="5" cols="6">
                <BaseQuestionTooltip :location="'top'" :text="'결제창 노출여부'"
                    :content="'상위 PG사, 할부한도, 수기결제 타입(수기결제 일 시)이 변경될 경우 가맹점에 꼭 결제창을 재생성하도록 고지해야합니다.'"/>
            </VCol>
            <VCol md="7">
                <BooleanRadio :radio="props.item.show_pay_view" @update:radio="props.item.show_pay_view = $event">
                    <template #true>노출</template>
                    <template #false>숨김</template>
                </BooleanRadio>
            </VCol>
        </VRow>
        <VRow v-if="props.item.module_type == 1">
            <VCol md="5" cols="7">
                <BaseQuestionTooltip :location="'top'" :text="'결제 허용 간격'"
                    :content="'중복결제 방지를 위해 결제 텀을 설정합니다.<br>동일 결제모듈+금액+카드번호 조건일 시 동작합니다.'"/>
            </VCol>
            <VCol md="7" cols="5">
                <VTextField prepend-inner-icon="material-symbols:shutter-speed-minus" v-model="props.item.payment_term_min"
                    type="number" suffix="분"
                    :rules="[requiredValidatorV2(props.item.payment_term_min, '결제 허용 간격')]" />
            </VCol>
        </VRow>
    </VCardItem>
</template>
<style scoped>
:deep(.v-row) {
  align-items: center;
}
</style>
