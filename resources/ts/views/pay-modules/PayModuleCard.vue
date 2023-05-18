<script lang="ts" setup>
import axios from '@axios';
import { requiredValidator } from '@validators';
import type { MerchandisePropertie } from '@/views/types'
import AlertDialog from '@/views/utils/AlertDialog.vue';
import type { PayModule } from '@/views/types'

interface Props {
    item        : PayModule,
    pgs         : object,
    pg_secs     : object,
    pay_conds   : object,
    comm_calcs  : object,
}
const props = defineProps<Props>();
const module_types = [
  {id: 0, title: "단말기"}, {id: 1, title: "수기결제"},
  {id: 2, title: "인증결제"}, {id: 3, title: "간편결제"},
]
const installments = [
    {id: 0, title: "일시불"}, {id: 2, title: "2개월"},
    {id: 3, title: "3개월"}, {id: 4, title: "4개월"},
    {id: 5, title: "5개월"}, {id: 6, title: "6개월"},
    {id: 7, title: "7개월"}, {id: 8, title: "8개월"},
    {id: 9, title: "9개월"}, {id: 10, title: "10개월"},
    {id: 11, title: "11개월"}, {id: 12, title: "12개월"},
]
// ------------
</script>
<template>
    <AppCardActions
        action-collapsed
        :title="props.item.note"
        :collapsed="true"
    >    
    <VCard>
        <div class="d-flex justify-space-between flex-wrap flex-md-nowrap flex-column flex-md-row">
            <VCol cols="12" md="3">
                <VCardItem>
                <VCardTitle style="margin-bottom: 1em;">결제타입</VCardTitle>
                <!-- 👉 결제 모듈 타입 -->
                <VRow class="pt-3">
                    <VCol>
                        <label>결제모듈 타입</label>
                    </VCol>
                    <VCol>
                        <VSelect :menu-props="{ maxHeight: 400 }"
                            v-model="props.item.module_type" 
                            :items="module_types" 
                            prepend-inner-icon="ic-outline-send-to-mobile"
                            label="결제모듈 선택"
                            item-title="title" 
                            item-value="id"
                            single-line
                        />
                    </VCol>
                </VRow>
                <!-- 👉 수기결제 타입(구인증, 비인증) -->
                <VRow class="pt-3">
                    <VCol>
                        <label>수기결제 타입</label>
                    </VCol>
                    <VCol>
                        <VRadioGroup v-model="is_old_auth" inline>
                            <VRadio :value="true">
                                <template #label>
                                    <span>
                                        구인증
                                    </span>
                                </template>
                            </VRadio>
                            <VRadio :value="false">
                                <template #label>
                                    <span>
                                        비인증
                                    </span>
                                </template>
                            </VRadio>
                        </VRadioGroup>
                    </VCol>
                </VRow>
                <!-- 👉 할부한도 (수기,인증,간편,실시간,비인증) -->
                <VRow class="pt-3">
                    <VCol>
                        <label>할부한도</label>
                    </VCol>
                    <VCol>
                        <VSelect :menu-props="{ maxHeight: 400 }"
                            v-model="props.item.installment" 
                            :items="installments" 
                            prepend-inner-icon="fluent-credit-card-clock-20-regular" 
                            label="결제모듈 선택"
                            item-title="title" 
                            item-value="id"
                            single-line
                        />
                    </VCol>
                </VRow>
                <!-- 👉 PG사 -->
                <VRow class="pt-3">
                    <VCol>
                        <label>PG사</label>
                    </VCol>
                    <VCol>
                        <VSelect :menu-props="{ maxHeight: 400 }"
                            v-model="props.item.pg" 
                            :items="props.pgs" 
                            prepend-inner-icon="ph-buildings"
                            label="PG사 선택"
                            item-title="title" 
                            item-value="id"
                            single-line
                        />
                    </VCol>
                </VRow>
                <!-- 👉 PG 구간 -->
                <VRow class="pt-3">
                    <VCol>
                        <label>구간</label>
                    </VCol>
                    <VCol>
                        <VSelect :menu-props="{ maxHeight: 400 }"
                            v-model="props.item.pg_sec" 
                            :items="props.pg_secs" 
                            prepend-inner-icon="mdi-vector-intersection"
                            label="구간 선택"
                            item-title="title" 
                            item-value="id"
                            single-line
                        />
                    </VCol>
                </VRow>
                <!-- 👉 출금 ID -->
                <VRow class="pt-3">
                    <VCol>
                        <label>결제조건</label>
                    </VCol>
                    <VCol>
                        <VSelect :menu-props="{ maxHeight: 400 }"
                            v-model="props.item.pay_cond" 
                            :items="props.pay_conds" 
                            prepend-inner-icon="ic-outline-send-to-mobile"                                    
                            label="구간 선택"
                            item-title="title" 
                            item-value="id"
                            single-line
                        />
                    </VCol>
                </VRow>
                </VCardItem>
            </VCol>
            <VDivider :vertical="$vuetify.display.mdAndUp" />
            <VCol cols="12" md="3">
                <VCardItem>
                <VCardTitle style="margin-bottom: 1em;">결제정보</VCardTitle>
                <!-- 👉 API KEY-->
                <VRow class="pt-3">
                    <VCol>
                        <label>API KEY</label>
                    </VCol>
                    <VCol>
                    <VTextField
                        type="text"
                        v-model="props.item.api_key"
                        prepend-inner-icon="ic-baseline-vpn-key"
                        placeholder="API KEY 입력"
                        persistent-placeholder
                    />
                    </VCol>
                </VRow>

                <!-- 👉 SUB KEY-->
                <VRow class="pt-3">
                    <VCol>
                        <label>SUB KEY</label>
                    </VCol>
                    <VCol>
                    <VTextField
                        type="text"
                        v-model="props.item.sub_key"
                        prepend-inner-icon="ic-sharp-key"
                        placeholder="API KEY 입력"
                        persistent-placeholder
                    />
                    </VCol>
                </VRow>
                <!-- 👉 MID -->
                <VRow class="pt-3">
                    <VCol>
                        <label>MID</label>
                    </VCol>
                    <VCol>
                    <VTextField
                        type="text"
                        v-model="props.item.mid"
                        prepend-inner-icon="tabler-user"
                        placeholder="MID 입력"
                        persistent-placeholder
                    />
                    </VCol>
                </VRow>
                <!-- 👉 TID -->
                <VRow class="pt-3">
                    <VCol>
                        <label>TID</label>
                    </VCol>
                    <VCol>
                    <VTextField
                        type="text"
                        v-model="props.item.tid"
                        prepend-inner-icon="jam-key-f"
                        placeholder="TID 입력"
                        persistent-placeholder
                    />
                    </VCol>
                </VRow>

                <!-- 👉 시리얼 번호 -->
                <VRow class="pt-3">
                    <VCol>
                        <label>시리얼번호</label>
                    </VCol>
                    <VCol>
                    <VTextField
                        type="text"
                        v-model="props.item.serial_num"
                        prepend-inner-icon="ic-twotone-stay-primary-portrait"
                        placeholder="시리얼번호 입력"
                        persistent-placeholder
                    />
                    </VCol>
                </VRow>
                </VCardItem>
            </VCol>
            <VDivider :vertical="$vuetify.display.mdAndUp" />
            <VCol cols="12" md="3">
                <VCardItem>
                <VCardTitle style="margin-bottom: 1em;">단말기정보</VCardTitle>
                <!-- 통신비 -->
                <VRow class="pt-3">
                    <VCol>
                        <label>통신비</label>
                    </VCol>
                    <VCol>
                    <VTextField
                        type="number"
                        v-model="props.item.comm_pr"
                        prepend-inner-icon="tabler-currency-won"
                        placeholder="통신비 입력"
                        persistent-placeholder
                    />
                    </VCol>
                </VRow>
                <!-- 👉 정산일 -->
                <VRow class="pt-3">
                    <VCol>
                        <label>정산일</label>
                    </VCol>
                    <VCol>
                    <AppDateTimePicker
                        v-model="props.item.comm_calc_day"
                        prepend-inner-icon="ic-baseline-calendar-today"
                        label="정산일 입력"
                        single-line
                    />
                    </VCol>
                </VRow>
                <!-- 👉 정산주체 -->
                <VRow class="pt-3">
                    <VCol>
                        <label>정산주체</label>
                    </VCol>
                    <VCol>
                        <VAutocomplete :menu-props="{ maxHeight: 400 }" 
                            v-model="props.item.comm_calc" 
                            :items="props.comm_calcs"
                            prepend-inner-icon="tabler-man" label="정산자 선택"
                            item-title="sf_name" item-value="sf_id"
                            persistent-hint return-object single-line />
                    </VCol>
                </VRow>
                <!-- 👉 매출미달 차감금 -->
                <VRow class="pt-3">
                    <VCol>
                        <label>매출미달 차감금</label>
                    </VCol>
                    <VCol>
                    <VTextField
                        type="number"
                        v-model="props.item.under_sales_amt"
                        prepend-inner-icon="tabler-currency-won"
                        placeholder="매출미달 차감금 입력"
                        persistent-placeholder
                    />
                    </VCol>
                </VRow>
                <!-- 👉 개통일 -->
                <VRow class="pt-3">
                    <VCol>
                        <label>개통일</label>
                    </VCol>
                    <VCol>
                    <AppDateTimePicker
                        v-model="props.item.begin_dt"
                        prepend-inner-icon="ic-baseline-calendar-today"
                        label="개통일 입력"
                        single-line
                    />
                    </VCol>
                </VRow>
                <!-- 👉 출고일 -->
                <VRow class="pt-3">
                    <VCol>
                        <label>출고일</label>
                    </VCol>
                    <VCol>
                    <AppDateTimePicker
                        v-model="props.item.ship_out_dt"
                        prepend-inner-icon="ic-baseline-calendar-today"
                        label="출고일 입력"
                        single-line
                    />
                    </VCol>
                </VRow>
                <!-- 👉 출고상태 -->
                <VRow class="pt-3">
                    <VCol>
                        <label>출고상태</label>
                    </VCol>
                    <VCol>
                        <VRadioGroup v-model="props.item.ship_out_stat" inline>
                            <VRadio :value="true">
                                <template #label>
                                    <span>
                                        출고
                                    </span>
                                </template>
                            </VRadio>
                            <VRadio :value="false">
                                <template #label>
                                    <span>
                                        입고
                                    </span>
                                </template>
                            </VRadio>
                        </VRadioGroup>
                    </VCol>
                </VRow>
                </VCardItem>
            </VCol>
            <VDivider :vertical="$vuetify.display.mdAndUp" />
            <VCol cols="12" md="3">
                <VCardItem>
                <VCardTitle style="margin-bottom: 1em;">옵션</VCardTitle>
                <!-- 👉 매출전표 공급자 사용 여부 -->
                <VCol>
                    <label>매출전표 공급자 정보</label>
                </VCol>
                <VCol>
                    <VRadioGroup v-model="props.item.use_saleslip_prov" inline>
                        <VRadio :value="true">
                            <template #label>
                                <span>
                                    본사
                                </span>
                            </template>
                        </VRadio>
                        <VRadio :value="false">
                            <template #label>
                                <span>
                                    가맹점
                                </span>
                            </template>
                        </VRadio>
                    </VRadioGroup>
                </VCol>
                <!-- 👉 매출전표 판매자 사용 여부 -->
                <VCol>
                    <label>매출전표 판매자 정보</label>
                </VCol>
                <VCol>
                    <VRadioGroup v-model="props.item.use_saleslip_sell" inline>
                        <VRadio :value="true">
                            <template #label>
                                <span>
                                    본사
                                </span>
                            </template>
                        </VRadio>
                        <VRadio :value="false">
                            <template #label>
                                <span>
                                    가맹점
                                </span>
                            </template>
                        </VRadio>
                    </VRadioGroup>
                </VCol>
                <!-- 👉 비고 -->
                
                <VCol>
                    <label>비고</label>
                </VCol>
                <VCol>
                    <VTextarea
                        v-model="props.item.note"
                        counter
                        label="비고(명칭)"
                    />
                </VCol>
            </VCardItem>
            </VCol>
        </div>      
    </VCard>
    </AppCardActions>
</template>
