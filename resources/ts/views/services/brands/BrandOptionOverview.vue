<script setup lang="ts">

import { requiredValidator } from '@validators'
import type { FreeOption, PaidOption, AuthOption, Brand } from '@/views/types'
import CreateHalfVCol from '@/layouts/utils/CreateHalfVCol.vue'
import BooleanRadio from '@/layouts/utils/BooleanRadio.vue'
import { user_info } from '@/plugins/axios';

interface Props {
    item: {
        free: FreeOption,
        paid: PaidOption,
        auth: AuthOption,
    },
    brand: Brand,
}
const props = defineProps<Props>()
const md = user_info.value.level == 50 ? 6 : 12
// 화면 타입은 영업점 개별 선택
</script>
<template>
    <VRow class="match-height">
        <VCol cols="12" :md="md">
            <VCard>
                <VCardItem>
                    <VCardTitle>무료 옵션</VCardTitle>
                    <VRow class="pt-5">
                    </VRow>
                    <VRow class="pt-5">
                        <CreateHalfVCol :mdl="3" :mdr="9">
                            <template #name><span></span>수기결제 사용여부</template>
                            <template #input>
                                <BooleanRadio :radio="props.item.free.use_hand_pay" @update:radio="props.item.free.use_hand_pay = $event">
                                    <template #true>사용</template>
                                    <template #false>미사용</template>
                                </BooleanRadio>
                            </template>
                        </CreateHalfVCol>
                    </VRow>
                    <VRow class="pt-5">
                        <CreateHalfVCol :mdl="3" :mdr="9">
                            <template #name><span></span>인증결제 사용여부</template>
                            <template #input>
                                <BooleanRadio :radio="props.item.free.use_auth_pay" @update:radio="props.item.free.use_auth_pay = $event">
                                    <template #true>사용</template>
                                    <template #false>미사용</template>
                                </BooleanRadio>
                            </template>
                        </CreateHalfVCol>
                    </VRow>

                    <VRow class="pt-5">
                        <CreateHalfVCol :mdl="3" :mdr="9">
                            <template #name><span></span>간편결제 사용여부</template>
                            <template #input>
                                <BooleanRadio :radio="props.item.free.use_simple_pay" @update:radio="props.item.free.use_simple_pay = $event">
                                    <template #true>사용</template>
                                    <template #false>미사용</template>
                                </BooleanRadio>
                            </template>
                        </CreateHalfVCol>
                    </VRow>
                    <VRow class="pt-5">
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
                                        v-model="props.item.free.sales_slip.merchandise.addr"
                                        placeholder="주소를 입력해주세요." type="text" class='pt-3' />
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
                    <VCardTitle>유료 옵션</VCardTitle>
                    <VRow class="pt-5">
                        <CreateHalfVCol :mdl="3" :mdr="9">
                            <template #name><span></span>입금일</template>
                            <template #input>
                                <VTextField prepend-inner-icon="tabler-calendar" v-model="props.brand.deposit_day"
                                    type="number" :rules="[requiredValidator]" />
                            </template>
                        </CreateHalfVCol>
                    </VRow>
                    <VRow class="pt-5">
                        <CreateHalfVCol :mdl="3" :mdr="9">
                            <template #name><span></span>입금액</template>
                            <template #input>
                                <VTextField prepend-inner-icon="tabler-currency-won" v-model="props.brand.deposit_amount"
                                    type="number" :rules="[requiredValidator]" />
                            </template>
                        </CreateHalfVCol>
                    </VRow>
                    <VRow class="pt-5">
                        <CreateHalfVCol :mdl="3" :mdr="9">
                            <template #name><span></span>예금주 검증</template>
                            <template #input>
                                <BooleanRadio :radio="props.item.paid.use_acct_verification" @update:radio="props.item.paid.use_acct_verification = $event">
                                    <template #true>사용</template>
                                    <template #false>미사용</template>
                                </BooleanRadio>
                            </template>
                        </CreateHalfVCol>
                    </VRow>
                    <VRow class="pt-5">
                        <CreateHalfVCol :mdl="3" :mdr="9">
                            <template #name><span></span>수기결제 직접입력(가맹점)</template>
                            <template #input>
                                <BooleanRadio :radio="props.item.paid.use_hand_pay_drct" @update:radio="props.item.paid.use_hand_pay_drct = $event">
                                    <template #true>사용</template>
                                    <template #false>미사용</template>
                                </BooleanRadio>
                            </template>
                        </CreateHalfVCol>
                    </VRow>
                    <VRow class="pt-5">
                        <CreateHalfVCol :mdl="3" :mdr="9">
                            <template #name><span></span>수기결제 SMS</template>
                            <template #input>
                                <BooleanRadio :radio="props.item.paid.use_hand_pay_sms" @update:radio="props.item.paid.use_hand_pay_sms = $event">
                                    <template #true>사용</template>
                                    <template #false>미사용</template>
                                </BooleanRadio>
                            </template>
                        </CreateHalfVCol>
                    </VRow>
                    <VRow class="pt-5">
                        <CreateHalfVCol :mdl="3" :mdr="9">
                            <template #name><span></span>실시간 결제모듈</template>
                            <template #input>
                                <BooleanRadio :radio="props.item.paid.use_realtime_deposit" @update:radio="props.item.paid.use_realtime_deposit = $event">
                                    <template #true>사용</template>
                                    <template #false>미사용</template>
                                </BooleanRadio>
                            </template>
                        </CreateHalfVCol>
                    </VRow>
                    <VRow class="pt-5">
                        <CreateHalfVCol :mdl="3" :mdr="9">
                            <template #name><span></span>카드사 필터링</template>
                            <template #input>
                                <BooleanRadio :radio="props.item.paid.use_issuer_filter" @update:radio="props.item.paid.use_issuer_filter = $event">
                                    <template #true>사용</template>
                                    <template #false>미사용</template>
                                </BooleanRadio>
                            </template>
                        </CreateHalfVCol>
                    </VRow>
                    <VRow class="pt-5">
                        <CreateHalfVCol :mdl="3" :mdr="9">
                            <template #name><span></span>중복결제 검증</template>
                            <template #input>
                                <BooleanRadio :radio="props.item.paid.use_dup_pay_validation" @update:radio="props.item.paid.use_dup_pay_validation = $event">
                                    <template #true>사용</template>
                                    <template #false>미사용</template>
                                </BooleanRadio>
                            </template>
                        </CreateHalfVCol>
                    </VRow>
                    <VRow class="pt-5">
                        <CreateHalfVCol :mdl="3" :mdr="9">
                            <template #name><span></span>결제금지시간 지정</template>
                            <template #input>
                                <BooleanRadio :radio="props.item.paid.use_forb_pay_time" @update:radio="props.item.paid.use_forb_pay_time = $event">
                                    <template #true>사용</template>
                                    <template #false>미사용</template>
                                </BooleanRadio>
                            </template>
                        </CreateHalfVCol>
                    </VRow>
                    <VRow class="pt-5">
                        <CreateHalfVCol :mdl="3" :mdr="9">
                            <template #name><span></span>결제한도 지정</template>
                            <template #input>
                                <BooleanRadio :radio="props.item.paid.use_pay_limit" @update:radio="props.item.paid.use_pay_limit = $event">
                                    <template #true>사용</template>
                                    <template #false>미사용</template>
                                </BooleanRadio>
                            </template>
                        </CreateHalfVCol>
                    </VRow>
                    <VRow class="pt-5">
                        <CreateHalfVCol :mdl="3" :mdr="9">
                            <template #name><span></span>가맹점 전산 사용 ON/OFF</template>
                            <template #input>
                                <BooleanRadio :radio="props.item.paid.subsidiary_use_control" @update:radio="props.item.paid.subsidiary_use_control = $event">
                                    <template #true>사용</template>
                                    <template #false>미사용</template>
                                </BooleanRadio>
                            </template>
                        </CreateHalfVCol>
                    </VRow>
                    <VRow class="pt-5">
                        <CreateHalfVCol :mdl="3" :mdr="9">
                            <template #name><span></span>온라인 결제 사용여부</template>
                            <template #input>
                                <BooleanRadio :radio="props.item.paid.use_online_pay" @update:radio="props.item.paid.use_online_pay = $event">
                                    <template #true>사용</template>
                                    <template #false>미사용</template>
                                </BooleanRadio>
                            </template>
                        </CreateHalfVCol>
                    </VRow> 
                    <VRow class="pt-5">
                        <CreateHalfVCol :mdl="3" :mdr="9">
                            <template #name><span></span>TID 발급버튼 사용여부</template>
                            <template #input>
                                <BooleanRadio :radio="props.item.paid.use_tid_create" @update:radio="props.item.paid.use_tid_create = $event">
                                    <template #true>사용</template>
                                    <template #false>미사용</template>
                                </BooleanRadio>
                            </template>
                        </CreateHalfVCol>
                    </VRow>
                    <VRow class="pt-5">
                        <CreateHalfVCol :mdl="3" :mdr="9">
                            <template #name><span></span>MID 일괄적용 사용여부</template>
                            <template #input>
                                <BooleanRadio :radio="props.item.paid.use_mid_batch" @update:radio="props.item.paid.use_mid_batch = $event">
                                    <template #true>사용</template>
                                    <template #false>미사용</template>
                                </BooleanRadio>
                            </template>
                        </CreateHalfVCol>
                    </VRow>
                    <VRow class="pt-5">
                        <CreateHalfVCol :mdl="3" :mdr="9">
                            <template #name><span></span>TID 일괄적용 사용여부</template>
                            <template #input>
                                <BooleanRadio :radio="props.item.paid.use_tid_batch" @update:radio="props.item.paid.use_tid_batch = $event">
                                    <template #true>사용</template>
                                    <template #false>미사용</template>
                                </BooleanRadio>
                            </template>
                        </CreateHalfVCol>
                    </VRow>
                </VCardItem>
            </VCard>
        </VCol>
    </VRow>
</template>
