<script lang="ts" setup>
import type { PayModule } from '@/views/types'
import {
    abnormal_trans_limits, cxl_types
} from '@/views/merchandises/pay-modules/useStore'
import BooleanRadio from '@/layouts/utils/BooleanRadio.vue'
import CreateHalfVCol from '@/layouts/utils/CreateHalfVCol.vue'
import BaseQuestionTooltip from '@/layouts/tooltips/BaseQuestionTooltip.vue'
import { requiredValidatorV2 } from '@validators'
import { issuers } from '@/views/complaints/useStore'
interface Props {
    item: PayModule,
}

const props = defineProps<Props>()

</script>
<template>
    <VCardItem>
        <VRow>
            <CreateHalfVCol :mdl="6" :mdr="6">
                <template #name>취소타입
                </template>
                <template #input>
                    <VSelect v-model="props.item.cxl_type" :items="cxl_types" prepend-inner-icon="tabler:world-cancel"
                        label="취소타입 설정" item-title="title" item-value="id" single-line />
                </template>
            </CreateHalfVCol>
            <CreateHalfVCol :mdl="6" :mdr="6">
                <template #name>
                    <BaseQuestionTooltip :location="'top'" :text="'이상거래 한도설정'"
                        :content="'설정 금액 이상으로 결제가 발생할 시, 이상거래 관리 목록에 추가됩니다.'">
                    </BaseQuestionTooltip>
                </template>
                <template #input>
                    <VSelect v-model="props.item.abnormal_trans_limit" :items="abnormal_trans_limits"
                        prepend-inner-icon="jam-triangle-danger" label="이상거래 한도설정" item-title="title" item-value="id"
                        single-line />
                </template>
            </CreateHalfVCol>
            <CreateHalfVCol :mdl="6" :mdr="6">
                <template #name>
                    <BaseQuestionTooltip :location="'top'" :text="'중복거래 하한금'"
                        :content="'설정 금액 이하로 결제가 발생하는 건은 중복거래 탐지에서 무시됩니다.'">
                    </BaseQuestionTooltip>
                </template>
                <template #input>
                    <VTextField type="number" v-model="props.item.pay_dupe_least"
                        prepend-inner-icon="tabler-currency-won" placeholder="이상거래 상한금 입력" persistent-placeholder
                        suffix="만원" />
                </template>
            </CreateHalfVCol>
        </VRow>
        <VRow>
            <CreateHalfVCol :mdl="6" :mdr="6">
                <template #name>
                    <BaseQuestionTooltip :location="'top'" :text="'중복결제 허용회수'"
                        :content="'입력된 카드번호를 통해 중복해서 결제가되었는지 검증합니다.<br>0 입력 시 허용회수를 검증하지 않으며, <b>온라인 결제</b>만 적용 가능합니다.'">
                    </BaseQuestionTooltip>
                </template>
                <template #input>
                    <VTextField v-model="props.item.pay_dupe_limit" label="중복결제 허용회수" type="number" suffix="회 허용"
                        :rules="[requiredValidatorV2(props.item.pay_dupe_limit, '중복결제 허용회수')]" />
                </template>
            </CreateHalfVCol>
        </VRow>

        <VRow>
            <CreateHalfVCol :mdl="6" :mdr="6">
                <template #name>
                    <BaseQuestionTooltip :location="'top'" :text="'단건 결제 한도'"
                        :content="'결제 한도 금액: 1,000,000원 = 100 입력(이하동일)<br><b>온라인 결제</b>만 적용 가능합니다.'">
                    </BaseQuestionTooltip>
                </template>
                <template #input>
                    <VTextField prepend-inner-icon="tabler-currency-won" v-model="props.item.pay_single_limit"
                        type="number" suffix="만원"
                        :rules="[requiredValidatorV2(props.item.pay_single_limit, '단건 결제 한도')]" />
                </template>
            </CreateHalfVCol>
        </VRow>
        <VRow>
            <CreateHalfVCol :mdl="6" :mdr="6">
                <template #name>일 결제 한도</template>
                <template #input>
                    <VTextField prepend-inner-icon="tabler-currency-won" v-model="props.item.pay_day_limit"
                        type="number" suffix="만원" :rules="[requiredValidatorV2(props.item.pay_day_limit, '일 결제 한도')]" />
                </template>
            </CreateHalfVCol>
        </VRow>
        <VRow>
            <CreateHalfVCol :mdl="6" :mdr="6">
                <template #name>월 결제 한도</template>
                <template #input>
                    <VTextField prepend-inner-icon="tabler-currency-won" v-model="props.item.pay_month_limit"
                        type="number" suffix="만원"
                        :rules="[requiredValidatorV2(props.item.pay_month_limit, '월 결제 한도')]" />
                </template>
            </CreateHalfVCol>
        </VRow>
        <VRow>
            <CreateHalfVCol :mdl="6" :mdr="6">
                <template #name>연 결제 한도</template>
                <template #input>
                    <VTextField prepend-inner-icon="tabler-currency-won" v-model="props.item.pay_year_limit"
                        type="number" suffix="만원"
                        :rules="[requiredValidatorV2(props.item.pay_year_limit, '연 결제 한도')]" />
                </template>
            </CreateHalfVCol>
        </VRow>
        <VRow>
            <CreateHalfVCol :mdl="6" :mdr="6">
                <template #name>
                    <BaseQuestionTooltip :location="'top'" :text="'결제금지 시간'"
                        :content="'해당 시간대에는 <b>온라인 결제</b>를 발생시킬 수 없습니다.'">
                    </BaseQuestionTooltip>
                </template>
                <template #input>
                    <div class="d-flex align-items-center flex-column">
                        <VTextField v-model="props.item.pay_disable_s_tm" type="time" />
                        <span class="text-center mx-auto">~</span>
                        <VTextField v-model="props.item.pay_disable_e_tm" type="time" />
                    </div>
                </template>
            </CreateHalfVCol>
        </VRow>
        <VRow>
            <CreateHalfVCol :mdl="6" :mdr="6">
                <template #name>
                    <BaseQuestionTooltip :location="'top'" :text="'발급사 필터링'" :content="'해당 발급사로 결제된 카드는 강제취소됩니다.'">
                    </BaseQuestionTooltip>
                </template>
                <template #input>
                    <VAutocomplete :menu-props="{ maxHeight: 400 }" v-model="props.item.filter_issuers"
                        label="필터링할 발급사 선택" :items="issuers" prepend-inner-icon="ph-buildings" item-title="title"
                        item-value="code" multiple chips closable-chips single-line />
                </template>
            </CreateHalfVCol>
        </VRow>
        <VRow v-if="props.item.module_type != 0">
            <CreateHalfVCol :mdl="6" :mdr="6">
                <template #name>결제창 노출여부</template>
                <template #input>
                    <BooleanRadio :radio="props.item.show_pay_view" @update:radio="props.item.show_pay_view = $event">
                        <template #true>노출</template>
                        <template #false>숨김</template>
                    </BooleanRadio>
                </template>
            </CreateHalfVCol>
        </VRow>
    </VCardItem>
</template>
