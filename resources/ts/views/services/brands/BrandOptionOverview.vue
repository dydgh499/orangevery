<script setup lang="ts">

import type { FreeOption, PaidOption, AuthOption } from '@/views/types'
import CreateHalfVCol from '@/layouts/utils/CreateHalfVCol.vue'
import { user_info } from '@/plugins/axios';
import BaseQuestionTooltip from '@/layouts/tooltips/BaseQuestionTooltip.vue'
import corp from '@corp';

interface Props {
    item: {
        free: FreeOption,
        paid: PaidOption,
        auth: AuthOption,
    },
}
const props = defineProps<Props>()
const md = user_info.value.level == 50 ? 4 : 12
// 화면 타입은 영업점 개별 선택
</script>
<template>
    <VRow class="match-height">
        <VCol cols="12" :md="md">
            <VCard>
                <VCardItem>
                    <VCardTitle>추가 옵션</VCardTitle>
                    <VRow class="pt-5">
                        <CreateHalfVCol :mdl="6" :mdr="6">
                            <template #name>수기결제 사용여부</template>
                            <template #input>
                                <VSwitch v-model="props.item.free.use_hand_pay" color="primary" />
                            </template>
                        </CreateHalfVCol>
                    </VRow>
                    <VRow>
                        <CreateHalfVCol :mdl="6" :mdr="6">
                            <template #name>인증결제 사용여부</template>
                            <template #input>
                                <VSwitch v-model="props.item.free.use_auth_pay" color="primary" />
                            </template>
                        </CreateHalfVCol>
                    </VRow>

                    <VRow>
                        <CreateHalfVCol :mdl="6" :mdr="6">
                            <template #name>간편결제 사용여부</template>
                            <template #input>
                                <VSwitch v-model="props.item.free.use_simple_pay" color="primary" />
                            </template>
                        </CreateHalfVCol>
                    </VRow>
                    <VRow>
                        <VCol cols="12">
                            <VRow no-gutters>
                                <VCol cols="12" md="6">
                                    <label>
                                        매출전표 가맹점표기 정보
                                    </label>
                                </VCol>
                                <VCol cols="12" md="6">
                                    <VTextField prepend-inner-icon="ph-buildings"
                                        v-model="props.item.free.sales_slip.merchandise.comepany_name"
                                        placeholder="회사명을 입력해주세요." type="text" class='pt-3' />
                                    <VTextField prepend-inner-icon="tabler-user"
                                        v-model="props.item.free.sales_slip.merchandise.rep_name"
                                        placeholder="대표자명을 입력해주세요." type="text" class='pt-3' />
                                    <VTextField prepend-inner-icon="tabler-device-mobile"
                                        v-model="props.item.free.sales_slip.merchandise.phone_num"
                                        placeholder="연락처를 입력해주세요." type="text" class='pt-3' />
                                    <VTextField prepend-inner-icon="ic-outline-business-center"
                                        v-model="props.item.free.sales_slip.merchandise.business_num"
                                        placeholder="사업자등록번호를 입력해주세요." type="text" class='pt-3' />
                                    <VTextField prepend-inner-icon="tabler-map-pin"
                                        v-model="props.item.free.sales_slip.merchandise.addr" placeholder="주소를 입력해주세요."
                                        type="text" class='pt-3' />
                                </VCol>
                            </VRow>
                        </VCol>
                    </VRow>
                </VCardItem>
            </VCard>
        </VCol>
        <VCol cols="12" :md="md" v-show="user_info.level == 50">
            <VCard>
                <VCardItem>
                    <VCardTitle>
                        <BaseQuestionTooltip location="top" text="영업점 일괄적용(유료)"
                            :content="`${corp.pv_options.auth.levels.dev_name}만 확인 가능한 정보입니다.`">
                        </BaseQuestionTooltip>
                    </VCardTitle>
                    <VRow class="pt-5">
                        <CreateHalfVCol :mdl="6" :mdr="6">
                            <template #name>MID 일괄적용 사용여부</template>
                            <template #input>
                                <VSwitch v-model="props.item.paid.use_mid_batch" color="primary" />
                            </template>
                        </CreateHalfVCol>
                    </VRow>
                    <VRow>
                        <CreateHalfVCol :mdl="6" :mdr="6">
                            <template #name>TID 일괄적용 사용여부</template>
                            <template #input>
                                <VSwitch v-model="props.item.paid.use_tid_batch" color="primary" />
                            </template>
                        </CreateHalfVCol>
                    </VRow>
                    <VRow>
                        <CreateHalfVCol :mdl="6" :mdr="6">
                            <template #name>API KEY 일괄적용 사용여부</template>
                            <template #input>
                                <VSwitch v-model="props.item.paid.use_api_key_batch" color="primary" />
                            </template>
                        </CreateHalfVCol>
                    </VRow>
                    <VRow>
                        <CreateHalfVCol :mdl="6" :mdr="6">
                            <template #name>SUB KEY 일괄적용 사용여부</template>
                            <template #input>
                                <VSwitch v-model="props.item.paid.use_sub_key_batch" color="primary" />
                            </template>
                        </CreateHalfVCol>
                    </VRow>
                    <VCardTitle class="pt-10">
                        <BaseQuestionTooltip location="top" text="가맹점 옵션(유료)"
                            :content="`${corp.pv_options.auth.levels.dev_name}만 확인 가능한 정보입니다.`">
                        </BaseQuestionTooltip>
                    </VCardTitle>
                    <VRow class="pt-5">
                        <CreateHalfVCol :mdl="6" :mdr="6">
                            <template #name>예금주 검증</template>
                            <template #input>
                                <VSwitch v-model="props.item.paid.use_acct_verification" color="primary" />
                            </template>
                        </CreateHalfVCol>
                    </VRow>
                    <VRow>
                        <CreateHalfVCol :mdl="6" :mdr="6">
                            <template #name>가맹점 전산 사용 ON/OFF</template>
                            <template #input>
                                <VSwitch v-model="props.item.paid.subsidiary_use_control" color="primary" />
                            </template>
                        </CreateHalfVCol>
                    </VRow>
                    <VRow>
                        <CreateHalfVCol :mdl="6" :mdr="6">
                            <template #name>수기결제 직접입력(가맹점)</template>
                            <template #input>
                                <VSwitch v-model="props.item.paid.use_hand_pay_drct" color="primary" />
                            </template>
                        </CreateHalfVCol>
                    </VRow>
                    <VRow>
                        <CreateHalfVCol :mdl="6" :mdr="6">
                            <template #name>수기결제 SMS</template>
                            <template #input>
                                <VSwitch v-model="props.item.paid.use_hand_pay_sms" color="primary" />
                            </template>
                        </CreateHalfVCol>
                    </VRow>
                </VCardItem>
            </VCard>
        </VCol>
        <VCol cols="12" :md="md" v-show="user_info.level == 50">
            <VCard>
                <VCardItem>
                    <VCardTitle>
                        <BaseQuestionTooltip location="top" text="결제모듈 옵션(유료)"
                            :content="`${corp.pv_options.auth.levels.dev_name}만 확인 가능한 정보입니다.`">
                        </BaseQuestionTooltip>
                    </VCardTitle>
                    <VRow class="pt-5">
                        <CreateHalfVCol :mdl="6" :mdr="6">
                            <template #name>실시간 결제모듈</template>
                            <template #input>
                                <VSwitch v-model="props.item.paid.use_realtime_deposit" color="primary" />
                            </template>
                        </CreateHalfVCol>
                    </VRow>
                    <VRow>
                        <CreateHalfVCol :mdl="6" :mdr="6">
                            <template #name>카드사 필터링</template>
                            <template #input>
                                <VSwitch v-model="props.item.paid.use_issuer_filter" color="primary" />
                            </template>
                        </CreateHalfVCol>
                    </VRow>
                    <VRow>
                        <CreateHalfVCol :mdl="6" :mdr="6">
                            <template #name>중복결제 검증</template>
                            <template #input>
                                <VSwitch v-model="props.item.paid.use_dup_pay_validation" color="primary" />
                            </template>
                        </CreateHalfVCol>
                    </VRow>
                    <VRow>
                        <CreateHalfVCol :mdl="6" :mdr="6">
                            <template #name>결제금지시간 지정</template>
                            <template #input>
                                <VSwitch v-model="props.item.paid.use_forb_pay_time" color="primary" />
                            </template>
                        </CreateHalfVCol>
                    </VRow>
                    <VRow>
                        <CreateHalfVCol :mdl="6" :mdr="6">
                            <template #name>결제한도 지정</template>
                            <template #input>
                                <VSwitch v-model="props.item.paid.use_pay_limit" color="primary" />
                            </template>
                        </CreateHalfVCol>
                    </VRow>
                    <VRow>
                        <CreateHalfVCol :mdl="6" :mdr="6">
                            <template #name>온라인 결제 사용여부</template>
                            <template #input>
                                <VSwitch v-model="props.item.paid.use_online_pay" color="primary" />
                            </template>
                        </CreateHalfVCol>
                    </VRow>
                    <VRow>
                        <CreateHalfVCol :mdl="6" :mdr="6">
                            <template #name>TID 발급버튼 사용여부</template>
                            <template #input>
                                <VSwitch v-model="props.item.paid.use_tid_create" color="primary" />
                            </template>
                        </CreateHalfVCol>
                    </VRow>
                </VCardItem>
            </VCard>
        </VCol>
    </VRow>
</template>
