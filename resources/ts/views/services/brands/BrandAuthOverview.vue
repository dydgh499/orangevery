<script setup lang="ts">

import BaseQuestionTooltip from '@/layouts/tooltips/BaseQuestionTooltip.vue';
import CreateHalfVCol from '@/layouts/utils/CreateHalfVCol.vue';
import type { AuthOption, FreeOption, PaidOption } from '@/views/types';
import { getUserLevel } from '@axios';
import corp from '@corp';
import { requiredValidatorV2 } from '@validators';
interface Props {
    item: {
        free: FreeOption,
        paid: PaidOption,
        auth: AuthOption,
    },
}
const props = defineProps<Props>()

</script>
<template>
    <VRow class="match-height" v-if="getUserLevel() === 50">
        <VCol cols="12" md="6">
            <VCard>
                <VCardItem>
                    <VCardTitle>
                        <BaseQuestionTooltip location="top" text="브랜드 옵션(유료)"
                            :content="`${corp.pv_options.auth.levels.dev_name}만 확인 가능한 정보입니다.`">
                        </BaseQuestionTooltip>
                    </VCardTitle>
                    <VCol>
                        <VRow class="pt-5">
                            <CreateHalfVCol :mdl="6" :mdr="6">
                                <template #name>
                                    <VSwitch hide-details v-model="props.item.paid.use_noti" color="primary" label="노티 사용"/>
                                </template>
                                <template #input>
                                    <VSwitch hide-details v-model="props.item.paid.use_head_office_withdraw" color="primary" label="본사 지정계좌 출금"/>
                                </template>
                            </CreateHalfVCol>
                        </VRow>
                        <VRow>
                            <CreateHalfVCol :mdl="6" :mdr="6">
                                <template #name>
                                    <VSwitch hide-details v-model="props.item.auth.visibles.abnormal_trans_sales"
                                        color="primary" label="이상거래, 결제실패, 즉시출금 관리 영업점 노출여부"/>
                                </template>
                                <template #input>
                                    <div style="display: flex;">
                                        <VSwitch hide-details v-model="props.item.paid.use_before_brand_info" color="primary" label="기간별 사업자정보 사용"/>
                                        <BaseQuestionTooltip location="top" text=""
                                        :content="`기간별로 사업자정보가 매출전표에 표기됩니다.(공급자 정보: 본사정보로 체크되어야합니다.)`"/>
                                    </div>
                                </template>
                            </CreateHalfVCol>
                        </VRow>
                        <VRow>
                            <CreateHalfVCol :mdl="6" :mdr="6">
                                <template #name>
                                    <VSwitch hide-details v-model="props.item.paid.use_cancel_all_allow" color="primary" label="취소옵션 모두허용 추가"/>
                                </template>
                                <template #input>
                                    <VSwitch hide-details v-model="props.item.paid.use_part_cancel" color="primary" label="부분취소"/>
                                </template>
                            </CreateHalfVCol>
                        </VRow>
                        <VRow>
                            <CreateHalfVCol :mdl="6" :mdr="6">
                                <template #name>
                                    <VSwitch hide-details v-model="props.item.paid.use_mcht_blacklist" color="primary" label="가맹점 블랙리스트"/>
                                </template>
                                <template #input>
                                    <VSwitch hide-details v-model="props.item.paid.use_bill_key" color="primary" label="빌링결제"/>
                                </template>
                            </CreateHalfVCol>
                        </VRow>
                    </VCol>
                    <VCardTitle class="pt-10">
                        <BaseQuestionTooltip location="top" text="정산 옵션(유료)"
                            :content="`${corp.pv_options.auth.levels.dev_name}만 확인 가능한 정보입니다.`"/>
                    </VCardTitle>
                    <VCol>
                        <VRow class="pt-5">
                            <CreateHalfVCol :mdl="6" :mdr="6">
                                <template #name>
                                    <VSwitch hide-details v-model="props.item.paid.use_finance_van_deposit" color="primary" label="지급대행"/>
                                </template>
                                <template #input>
                                    <VSwitch hide-details v-model="props.item.paid.use_settle_hold" color="primary" label="지급보류"/>
                                </template>
                            </CreateHalfVCol>
                        </VRow>
                    </VCol>
                    <VCardTitle class="pt-10">
                        <BaseQuestionTooltip location="top" text="영업점 옵션(유료)"
                            :content="`${corp.pv_options.auth.levels.dev_name}만 확인 가능한 정보입니다.`"/>
                    </VCardTitle>
                    <VCol>
                        <VRow class="pt-5">
                            <CreateHalfVCol :mdl="6" :mdr="6">
                                <template #name>
                                    <VSwitch hide-details v-model="props.item.paid.sales_parent_structure" color="primary" label="계층형 구조"/>
                                </template>
                                <template #input>
                                </template>
                            </CreateHalfVCol>
                        </VRow>
                    </VCol>
                    <VCardTitle class="pt-10">
                        <BaseQuestionTooltip location="top" text="영업점 등급설정" :content="`${corp.pv_options.auth.levels.dev_name}만 확인 가능한 정보입니다.`"/>
                    </VCardTitle>
                    <VCol>
                        <VRow class="pt-5">
                            <VCol cols="6">
                                <VRow no-gutters style="align-items: center;">
                                    <VCol cols="12" :md="4">
                                        <span>영업자 등급 6</span>
                                    </VCol>
                                    <VCol cols="12" :md="8">
                                        <div style="display: flex;">
                                            <VTextField v-model="props.item.auth.levels.sales5_name"
                                                prepend-inner-icon="ph:share-network" placeholder="사용할 등급 명칭을 입력해주세요"
                                                persistent-placeholder :rules="[requiredValidatorV2(props.item.auth.levels.sales5_name, '등급6 명칭')]" 
                                                style="max-width: 10em;"/>
                                            <VSwitch hide-details v-model="item.auth.levels.sales5_use" color="primary"  style="margin-left: 1em;"/>
                                        </div>
                                    </VCol>
                                </VRow>
                            </VCol>

                            <VCol cols="6">
                                <VRow no-gutters style="align-items: center;">
                                    <VCol cols="12" :md="4">
                                        <span>영업자 등급 5</span>
                                    </VCol>
                                    <VCol cols="12" :md="8">
                                        <div style="display: flex;">
                                            <VTextField v-model="props.item.auth.levels.sales4_name"
                                                prepend-inner-icon="ph:share-network" placeholder="사용할 등급 명칭을 입력해주세요"
                                                persistent-placeholder :rules="[requiredValidatorV2(props.item.auth.levels.sales4_name, '등급5 명칭')]" 
                                                style="max-width: 10em;"/>
                                            <VSwitch hide-details v-model="item.auth.levels.sales4_use" color="primary"  style="margin-left: 1em;"/>
                                        </div>
                                    </VCol>
                                </VRow>
                            </VCol>
                        </VRow>

                        <VRow>
                            <VCol cols="6">
                                <VRow no-gutters style="align-items: center;">
                                    <VCol cols="12" :md="4">
                                        <span>영업자 등급 4</span>
                                    </VCol>
                                    <VCol cols="12" :md="8">
                                        <div style="display: flex;">
                                            <VTextField v-model="props.item.auth.levels.sales3_name"
                                                prepend-inner-icon="ph:share-network" placeholder="사용할 등급 명칭을 입력해주세요"
                                                persistent-placeholder :rules="[requiredValidatorV2(props.item.auth.levels.sales3_name, '등급4 명칭')]" 
                                                style="max-width: 10em;"/>
                                            <VSwitch hide-details v-model="item.auth.levels.sales3_use" color="primary"  style="margin-left: 1em;"/>
                                        </div>
                                    </VCol>
                                </VRow>
                            </VCol>

                            <VCol cols="6">
                                <VRow no-gutters style="align-items: center;">
                                    <VCol cols="12" :md="4">
                                        <span>영업자 등급 3</span>
                                    </VCol>
                                    <VCol cols="12" :md="8">
                                        <div style="display: flex;">
                                            <VTextField v-model="props.item.auth.levels.sales2_name"
                                                prepend-inner-icon="ph:share-network" placeholder="사용할 등급 명칭을 입력해주세요"
                                                persistent-placeholder :rules="[requiredValidatorV2(props.item.auth.levels.sales2_name, '등급3 명칭')]" 
                                                style="max-width: 10em;"/>
                                            <VSwitch hide-details v-model="item.auth.levels.sales2_use" color="primary"  style="margin-left: 1em;"/>                                        
                                        </div>
                                    </VCol>
                                </VRow>
                            </VCol>
                        </VRow>

                        <VRow>
                            <VCol cols="6">
                                <VRow no-gutters style="align-items: center;">
                                    <VCol cols="12" :md="4">
                                        <span>영업자 등급 2</span>
                                    </VCol>
                                    <VCol cols="12" :md="8">
                                        <div style="display: flex;">
                                            <VTextField v-model="props.item.auth.levels.sales1_name"
                                                prepend-inner-icon="ph:share-network" placeholder="사용할 등급 명칭을 입력해주세요"
                                                persistent-placeholder :rules="[requiredValidatorV2(props.item.auth.levels.sales1_name, '등급2 명칭')]" 
                                                style="max-width: 10em;"/>
                                            <VSwitch hide-details v-model="item.auth.levels.sales1_use" color="primary"  style="margin-left: 1em;"/>
                                        </div>
                                    </VCol>
                                </VRow>
                            </VCol>

                            <VCol cols="6">
                                <VRow no-gutters style="align-items: center;">
                                    <VCol cols="12" :md="4">
                                        <span>영업자 등급 1</span>
                                    </VCol>
                                    <VCol cols="12" :md="8">
                                        <div style="display: flex;">
                                            <VTextField v-model="props.item.auth.levels.sales0_name"
                                                prepend-inner-icon="ph:share-network" placeholder="사용할 등급 명칭을 입력해주세요"
                                                persistent-placeholder :rules="[requiredValidatorV2(props.item.auth.levels.sales0_name, '등급1 명칭')]" 
                                                style="max-width: 10em;"/>
                                            <VSwitch hide-details v-model="item.auth.levels.sales0_use" color="primary"  style="margin-left: 1em;"/>
                                        </div>
                                    </VCol>
                                </VRow>
                            </VCol>
                        </VRow>
                    </VCol>
                </VCardItem>
            </VCard>
        </VCol>
        <VCol cols="12" md="6">
            <VCard>
                <VCardItem>
                    <VCardTitle>
                        <BaseQuestionTooltip location="top" text="가맹점 옵션(유료)"
                            :content="`${corp.pv_options.auth.levels.dev_name}만 확인 가능한 정보입니다.`">
                        </BaseQuestionTooltip>
                    </VCardTitle>
                    <VCol>
                        <VRow class="pt-5">
                            <CreateHalfVCol :mdl="6" :mdr="6">
                                <template #name>
                                    <VSwitch hide-details v-model="props.item.paid.use_acct_verification" color="primary" label="예금주 검증"/>
                                </template>
                                <template #input>
                                    <VSwitch hide-details v-model="props.item.paid.subsidiary_use_control" color="primary" label="가맹점 전산 ON/OFF"/>
                                </template>
                            </CreateHalfVCol>
                        </VRow>
                        <VRow>
                            <CreateHalfVCol :mdl="6" :mdr="6">
                                <template #name>
                                    <VSwitch hide-details v-model="props.item.paid.use_hand_pay_sms" color="primary" label="결제창 SMS 발송"/>
                                </template>
                                <template #input>
                                    <VSwitch hide-details v-model="props.item.paid.use_pay_verification_mobile"
                                        color="primary" label="결제창 휴대폰 인증"/>
                                </template>
                            </CreateHalfVCol>
                        </VRow>
                        <VRow>
                            <CreateHalfVCol :mdl="6" :mdr="6">
                                <template #name>
                                    <VSwitch hide-details v-model="props.item.paid.use_collect_withdraw" color="primary" label="모아서 출금"/>
                                </template>
                                <template #input>
                                    <VSwitch hide-details v-model="props.item.paid.use_collect_withdraw_scheduler"
                                        color="primary" label="모아서 출금 스케줄링"/>
                                </template>
                            </CreateHalfVCol>
                        </VRow>
                        <VRow>
                            <CreateHalfVCol :mdl="6" :mdr="6">
                                <template #name>
                                    <VSwitch hide-details v-model="props.item.paid.use_regular_card" color="primary" label="단골고객 전용결제"/>
                                </template>
                                <template #input>
                                    <VSwitch hide-details v-model="props.item.paid.use_withdraw_fee" color="primary" label="출금 수수료"/>
                                </template>
                            </CreateHalfVCol>
                        </VRow>
                        <VRow>
                            <CreateHalfVCol :mdl="6" :mdr="6">
                                <template #name>
                                    <VSwitch hide-details v-model="props.item.paid.use_hide_account" color="primary" label="가맹점 계좌숨김"/>
                                </template>
                                <template #input>
                                    <VSwitch hide-details v-model="props.item.paid.use_shop" color="primary" label="쇼핑몰 사용"/>                                    
                                </template>
                            </CreateHalfVCol>
                        </VRow>
                        <VRow>
                            <CreateHalfVCol :mdl="6" :mdr="6">
                                <template #name>
                                    <div style="display: flex;">
                                        <VSwitch hide-details v-model="props.item.paid.use_multiple_hand_pay" color="primary" label="다중 수기결제"/>
                                        <BaseQuestionTooltip location="top" text=""
                                            :content="`사용 가맹점당 3개의 결제모듈이 존재해야 활성화 됩니다.`"/>
                                    </div>
                                </template>
                                <template #input>
                                    <div style="display: flex;">
                                        <VSwitch hide-details v-model="props.item.paid.use_specified_limit" color="primary" label="지정시간 결제제한"/>
                                        <BaseQuestionTooltip location="top" text=""
                                            :content="`지정시간대에 결제, 이체를 설정한 상한금 이상으로 할 수 없습니다.`"/>
                                    </div>
                                </template>
                            </CreateHalfVCol>
                        </VRow>
                        <VRow>
                            <CreateHalfVCol :mdl="6" :mdr="6">
                                <template #name>
                                    <VSwitch hide-details v-model="props.item.paid.use_syslink" color="primary" label="syslink 선정산"/>
                                </template>
                                <template #input>
                                    <VSwitch hide-details v-model="props.item.paid.use_product" color="primary" label="수기단말기 상품선택"/>
                                </template>
                            </CreateHalfVCol>
                        </VRow>

                    </VCol>
                    <VCardTitle class="pt-10">
                        <BaseQuestionTooltip location="top" text="결제모듈 옵션(유료)"
                            :content="`${corp.pv_options.auth.levels.dev_name}만 확인 가능한 정보입니다.`"/>
                    </VCardTitle>
                    <VCol>
                        <VRow class="pt-5">
                            <CreateHalfVCol :mdl="6" :mdr="6">
                                <template #name>
                                    <VSwitch hide-details v-model="props.item.paid.use_realtime_deposit" color="primary" label="실시간 결제모듈"/>
                                </template>
                                <template #input>
                                    <VSwitch hide-details v-model="props.item.paid.use_issuer_filter" color="primary" label="카드사 필터링"/>
                                </template>
                            </CreateHalfVCol>
                        </VRow>
                        <VRow>
                            <CreateHalfVCol :mdl="6" :mdr="6">
                                <template #name>
                                    <VSwitch hide-details v-model="props.item.paid.use_dup_pay_validation" color="primary" label="중복결제 검증"/>
                                </template>
                                <template #input>
                                    <VSwitch hide-details v-model="props.item.paid.use_forb_pay_time" color="primary" label="결제금지시간"/>
                                </template>
                            </CreateHalfVCol>
                        </VRow>
                        <VRow>
                            <CreateHalfVCol :mdl="6" :mdr="6">
                                <template #name>
                                    <VSwitch hide-details v-model="props.item.paid.use_pay_limit" color="primary" label="결제한도"/>
                                </template>
                                <template #input>
                                    <VSwitch hide-details v-model="props.item.paid.use_online_pay" color="primary" label="PAY KEY 사용"/>
                                </template>
                            </CreateHalfVCol>
                        </VRow>
                        <VRow>
                            <CreateHalfVCol :mdl="6" :mdr="6">
                                <template #name>
                                    <VSwitch hide-details v-model="props.item.paid.use_tid_create" color="primary" label="TID 발급버튼"/>
                                </template>
                                <template #input>
                                    <VSwitch hide-details v-model="props.item.paid.use_mid_create" color="primary" label="MID 발급버튼"/>
                                </template>
                            </CreateHalfVCol>
                        </VRow>
                        <VRow>
                            <CreateHalfVCol :mdl="6" :mdr="6">
                                <template #name>
                                    <VSwitch hide-details v-model="props.item.paid.use_pmid" color="primary" label="PMID 사용"/>
                                </template>
                                <template #input>
                                </template>
                            </CreateHalfVCol>
                        </VRow>
                    </VCol>
                </VCardItem>
            </VCard>
        </VCol>
    </VRow>
</template>
