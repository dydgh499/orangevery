<script setup lang="ts">

import { requiredValidator, nullValidator } from '@validators';
import type { Brand } from '@/views/types'
import CreateHalfVCol from '@/views/utils/CreateHalfVCol.vue';
import BooleanRadio from '@/views/utils/BooleanRadio.vue';

interface Props {
    item: Brand,
}
const props = defineProps<Props>()

// 화면 타입은 영업자 개별 선택
</script>
<template>
    <VRow class="match-height">
        <VCol cols="12" md="6">
            <VCard>
                <VCardItem>
                    <VCardTitle>무료 옵션</VCardTitle>
                    <VRow class="pt-5">
                        <CreateHalfVCol :mdl="3" :mdr="9">
                            <template #name><span></span>개발사 사용여부</template>
                            <template #input>
                                <BooleanRadio :radio="props.item.pv_options.free.use_hand_pay" @update:radio="props.item.pv_options.free.use_hand_pay = $event">
                                    <template #true>사용</template>
                                    <template #false>미사용</template>
                                </BooleanRadio>
                            </template>
                        </CreateHalfVCol>
                    </VRow>
                    <VRow class="pt-5">
                        <CreateHalfVCol :mdl="3" :mdr="9">
                            <template #name><span></span>수기결제 사용여부</template>
                            <template #input>
                                <BooleanRadio :radio="props.item.pv_options.free.use_hand_pay" @update:radio="props.item.pv_options.free.use_hand_pay = $event">
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
                                <BooleanRadio :radio="props.item.pv_options.free.use_auth_pay" @update:radio="props.item.pv_options.free.use_auth_pay = $event">
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
                                <BooleanRadio :radio="props.item.pv_options.free.use_simple_pay" @update:radio="props.item.pv_options.free.use_simple_pay = $event">
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
                                    <VTextField prepend-inner-icon="tabler-user"
                                        v-model="props.item.pv_options.free.sales_slip.merchandise.rep_nm"
                                        placeholder="대표자명을 입력해주세요." type="text" :rules="[requiredValidator]" />
                                    <VTextField prepend-inner-icon="tabler-device-mobile"
                                        v-model="props.item.pv_options.free.sales_slip.merchandise.phone_num"
                                        placeholder="연락처를 입력해주세요." type="text" :rules="[requiredValidator]" class='pt-3' />
                                    <VTextField prepend-inner-icon="ic-outline-business-center"
                                        v-model="props.item.pv_options.free.sales_slip.merchandise.business_num"
                                        placeholder="사업자번호를 입력해주세요." type="text" :rules="[requiredValidator]" class='pt-3' />
                                    <VTextField prepend-inner-icon="carbon-identification"
                                        v-model="props.item.pv_options.free.sales_slip.merchandise.resident_num"
                                        placeholder="주민등록번호를 입력해주세요." type="text" :rules="[requiredValidator]" class='pt-3' />
                                    <VTextField prepend-inner-icon="tabler-map-pin"
                                        v-model="props.item.pv_options.free.sales_slip.merchandise.addr"
                                        placeholder="주소를 입력해주세요." type="text" :rules="[requiredValidator]" class='pt-3' />
                                </VCol>
                            </VRow>
                        </VCol>
                    </VRow>
                </VCardItem>
            </VCard>
        </VCol>
        <VCol cols="12" md="6">
            <VCard>
                <VCardItem>
                    <VCardTitle>유료 옵션</VCardTitle>
                    <VRow class="pt-5">
                        <CreateHalfVCol :mdl="3" :mdr="9">
                            <template #name><span></span>입금일</template>
                            <template #input>
                                <VTextField prepend-inner-icon="tabler-currency-won" v-model="props.item.deposit_day"
                                    type="number" :rules="[requiredValidator]" />
                            </template>
                        </CreateHalfVCol>
                    </VRow>
                    <VRow class="pt-5">
                        <CreateHalfVCol :mdl="3" :mdr="9">
                            <template #name><span></span>입금액</template>
                            <template #input>
                                <VTextField prepend-inner-icon="tabler-currency-won" v-model="props.item.deposit_amount"
                                    type="number" :rules="[requiredValidator]" />
                            </template>
                        </CreateHalfVCol>
                    </VRow>
                    <VRow class="pt-5">
                        <CreateHalfVCol :mdl="3" :mdr="9">
                            <template #name><span></span>예금주 검증</template>
                            <template #input>
                                <BooleanRadio :radio="props.item.pv_options.paid.use_acct_verification" @update:radio="props.item.pv_options.paid.use_acct_verification = $event">
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
                                <BooleanRadio :radio="props.item.pv_options.paid.use_hand_pay_drct" @update:radio="props.item.pv_options.paid.use_hand_pay_drct = $event">
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
                                <BooleanRadio :radio="props.item.pv_options.paid.use_hand_pay_sms" @update:radio="props.item.pv_options.paid.use_hand_pay_sms = $event">
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
                                <BooleanRadio :radio="props.item.pv_options.paid.use_realtime_deposit">
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
                                <BooleanRadio :radio="props.item.pv_options.paid.use_issuer_filter" @update:radio="props.item.pv_options.paid.use_issuer_filter = $event">
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
                                <BooleanRadio :radio="props.item.pv_options.paid.use_dup_pay_validation" @update:radio="props.item.pv_options.paid.use_dup_pay_validation = $event">
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
                                <BooleanRadio :radio="props.item.pv_options.paid.use_forb_pay_time" @update:radio="props.item.pv_options.paid.use_forb_pay_time = $event">
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
                                <BooleanRadio :radio="props.item.pv_options.paid.use_pay_limit" @update:radio="props.item.pv_options.paid.use_pay_limit = $event">
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
                                <BooleanRadio :radio="props.item.pv_options.paid.subsidiary_use_control" @update:radio="props.item.pv_options.paid.subsidiary_use_control = $event">
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
