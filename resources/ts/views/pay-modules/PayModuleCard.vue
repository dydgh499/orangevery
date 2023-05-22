<script lang="ts" setup>
import { axios } from '@axios';
import { requiredValidator } from '@validators';
import type { PayModule } from '@/views/types'
import { VForm } from 'vuetify/components';
import { useStore } from '@/views/pay-gateways/useStore';
import { useSalesHierarchicalStore } from '@/views/salesforces/useStore'

interface Props {
    item: PayModule,
    ancestors:object[],
}
const vForm = ref<VForm>()
const props = defineProps<Props>();
const { hierarchical, flattened } = useSalesHierarchicalStore()

const module_types = [
    { id: 0, title: "단말기" }, { id: 1, title: "수기결제" },
    { id: 2, title: "인증결제" }, { id: 3, title: "간편결제" },
]
const installments = [
    { id: 0, title: "일시불" }, { id: 2, title: "2개월" },
    { id: 3, title: "3개월" }, { id: 4, title: "4개월" },
    { id: 5, title: "5개월" }, { id: 6, title: "6개월" },
    { id: 7, title: "7개월" }, { id: 8, title: "8개월" },
    { id: 9, title: "9개월" }, { id: 10, title: "10개월" },
    { id: 11, title: "11개월" }, { id: 12, title: "12개월" },
]
const { pgs, pss, pay_conds, ternimals } = useStore()
const md = ref<number>(3)

onMounted(() => {
    props.item.is_old_auth = Boolean(props.item.is_old_auth)
    props.item.ship_out_stat = Boolean(props.item.ship_out_stat)
    props.item.use_saleslip_prov = Boolean(props.item.use_saleslip_prov)
    props.item.use_saleslip_sell = Boolean(props.item.use_saleslip_sell)

    props.item.pg_id = props.item.pg_id == 0 ? null : props.item.pg_id
    props.item.ps_id = props.item.ps_id == 0 ? null : props.item.ps_id
    props.item.withdraw_id = props.item.withdraw_id == 0 ? null : props.item.withdraw_id
    props.item.terminal_id = props.item.terminal_id == 0 ? null : props.item.terminal_id
})

// 결제모듈 타입 변동 체크
watchEffect(() => {
    md.value = props.item.module_type == 0 ? 3 : 4
})

const filterSalesforces = computed(() => {
    return props.ancestors.length == 0 ? flattened : props.ancestors
})
const filterPgs = computed(() => {
    const filter = pss.filter(item => {
        return item.pg_id == props.item.pg_id;
    })
    if(pss.length > 0) {
        if(filter.length > 0) {
            let item = pss.find(item => item.id === props.item.ps_id)
            if(item != undefined && filter[0].pg_id != item.pg_id)
                props.item.ps_id = null
        }
        else
            props.item.ps_id = null
    }
    return filter
})
const setFee = (items: any, id: number) => {
    let item = items.find(item => item.id === id)
    return item != undefined ? "수수료율: " + (item.trx_fee * 100).toFixed(3) + "%" : ''
}
const setAmount = (items: any, id: number) => {
    let item = items.find(item => item.id === id)
    return item != undefined ? "이용 수수료: " + item.trx_fee + "₩" : ''
}
function update() {
    let url = '/api/v1/pay-modules'
    url += props.item.id ? "/" + props.item.id : ""
    vForm.value.validate()
    axios.post(url, props.item)
}
</script>
<template>
    <AppCardActions action-collapsed :title="props.item.note" :collapsed="true">
        <VDivider />
        <VForm ref="vForm">
            <div class="d-flex justify-space-between flex-wrap flex-md-nowrap flex-column flex-md-row">
                <VCol cols="12" :md="md">
                    <VCardItem>
                        <VCardTitle style="margin-bottom: 1em;">결제타입</VCardTitle>
                        <!-- 👉 결제 모듈 타입 -->
                        <VRow class="pt-3">
                            <VCol>
                                <label>결제모듈 타입</label>
                            </VCol>
                            <VCol>
                                <VSelect :menu-props="{ maxHeight: 400 }" v-model="props.item.module_type"
                                    :items="module_types" prepend-inner-icon="ic-outline-send-to-mobile" label="결제모듈 선택"
                                    item-title="title" item-value="id" single-line />
                            </VCol>
                        </VRow>
                        <!-- 👉 수기결제 타입(구인증, 비인증) -->
                        <VRow class="pt-3" v-show="props.item.module_type == 1">
                            <VCol>
                                <label>수기결제 타입</label>
                            </VCol>
                            <VCol>
                                <VRadioGroup v-model="props.item.is_old_auth" inline>
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
                                <VSelect :menu-props="{ maxHeight: 400 }" v-model="props.item.installment"
                                    :items="installments" prepend-inner-icon="fluent-credit-card-clock-20-regular"
                                    label="결제모듈 선택" item-title="title" item-value="id" single-line />
                            </VCol>
                        </VRow>
                        <!-- 👉 PG사 -->
                        <VRow class="pt-3">
                            <VCol>
                                <label>PG사</label>
                            </VCol>
                            <VCol>
                                <VSelect :menu-props="{ maxHeight: 400 }" v-model="props.item.pg_id" :items="pgs"
                                    prepend-inner-icon="ph-buildings" label="PG사 선택" item-title="pg_nm" item-value="id"
                                    single-line />
                            </VCol>
                        </VRow>
                        <!-- 👉 PG 구간 -->
                        <VRow class="pt-3">
                            <VCol>
                                <label>구간</label>
                            </VCol>
                            <VCol>
                                <VSelect :menu-props="{ maxHeight: 400 }" v-model="props.item.ps_id" :items="filterPgs"
                                    prepend-inner-icon="mdi-vector-intersection" label="구간 선택" item-title="name"
                                    item-value="id" :hint="`${setFee(pss, props.item.ps_id)}`" persistent-hint single-line>
                                </VSelect>
                            </VCol>
                        </VRow>
                        <!-- 👉 출금 ID -->
                        <VRow class="pt-3">
                            <VCol>
                                <label>결제조건</label>
                            </VCol>
                            <VCol>
                                <VSelect :menu-props="{ maxHeight: 400 }" v-model="props.item.withdraw_id"
                                    :items="pay_conds" prepend-inner-icon="ic-outline-send-to-mobile" label="결제조건 선택"
                                    item-title="name" item-value="id" single-line persistent-hint
                                    :hint="`${setAmount(pay_conds, props.item.withdraw_id)}`" />
                            </VCol>
                        </VRow>
                    </VCardItem>
                </VCol>
                <VDivider :vertical="$vuetify.display.mdAndUp" />
                <VCol cols="12" :md="md">
                    <VCardItem>
                        <VCardTitle style="margin-bottom: 1em;">결제정보</VCardTitle>
                        <!-- 👉 API KEY-->
                        <VRow class="pt-3">
                            <VCol>
                                <label>API KEY(license)</label>
                            </VCol>
                            <VCol>
                                <VTextField type="text" v-model="props.item.api_key"
                                    prepend-inner-icon="ic-baseline-vpn-key" placeholder="API KEY 입력"
                                    persistent-placeholder />
                            </VCol>
                        </VRow>

                        <!-- 👉 SUB KEY-->
                        <VRow class="pt-3">
                            <VCol>
                                <label>SUB KEY(aes)</label>
                            </VCol>
                            <VCol>
                                <VTextField type="text" v-model="props.item.sub_key" prepend-inner-icon="ic-sharp-key"
                                    placeholder="SUB KEY 입력" persistent-placeholder />
                            </VCol>
                        </VRow>
                        <!-- 👉 MID -->
                        <VRow class="pt-3">
                            <VCol>
                                <label>MID</label>
                            </VCol>
                            <VCol>
                                <VTextField type="text" v-model="props.item.mid" prepend-inner-icon="tabler-user"
                                    placeholder="MID 입력" persistent-placeholder />
                            </VCol>
                        </VRow>
                        <!-- 👉 TID -->
                        <VRow class="pt-3">
                            <VCol>
                                <label>TID</label>
                            </VCol>
                            <VCol>
                                <VTextField type="text" v-model="props.item.tid" prepend-inner-icon="jam-key-f"
                                    placeholder="TID 입력" persistent-placeholder />
                            </VCol>
                        </VRow>

                        <!-- 👉 시리얼 번호 -->
                        <VRow class="pt-3" v-show="props.item.module_type == 0">
                            <VCol>
                                <label>시리얼번호</label>
                            </VCol>
                            <VCol>
                                <VTextField type="text" v-model="props.item.serial_num"
                                    prepend-inner-icon="ic-twotone-stay-primary-portrait" placeholder="시리얼번호 입력"
                                    persistent-placeholder />
                            </VCol>
                        </VRow>
                    </VCardItem>
                </VCol>
                <VDivider :vertical="$vuetify.display.mdAndUp" v-show="props.item.module_type == 0" />
                <VCol cols="12" :md="md" v-show="props.item.module_type == 0">
                    <VCardItem>
                        <VCardTitle style="margin-bottom: 1em;">단말기정보</VCardTitle>
                        <!-- 단말기 종류 -->
                        <VRow class="pt-3">
                            <VCol>
                                <label>단말기 타입</label>
                            </VCol>
                            <VCol>
                                <VSelect :menu-props="{ maxHeight: 400 }" v-model="props.item.terminal_id"
                                    :items="ternimals" prepend-inner-icon="ic-outline-send-to-mobile" label="단말기 선택"
                                    item-title="name" item-value="id" single-line persistent-hint
                                    :hint="`${setAmount(ternimals, props.item.terminal_id)}`" />
                            </VCol>
                        </VRow>
                        <!-- 통신비 -->
                        <VRow class="pt-3">
                            <VCol>
                                <label>통신비</label>
                            </VCol>
                            <VCol>
                                <VTextField type="number" v-model="props.item.comm_pr"
                                    prepend-inner-icon="tabler-currency-won" placeholder="통신비 입력" persistent-placeholder />
                            </VCol>
                        </VRow>
                        <!-- 👉 정산일 -->
                        <VRow class="pt-3">
                            <VCol>
                                <label>정산일</label>
                            </VCol>
                            <VCol>
                                <AppDateTimePicker v-model="props.item.comm_calc_day"
                                    prepend-inner-icon="ic-baseline-calendar-today" label="정산일 입력" single-line />
                            </VCol>
                        </VRow>
                        <!-- 👉 정산주체 -->
                        <VRow class="pt-3">
                            <VCol>
                                <label>정산주체</label>
                            </VCol>
                            <VCol>
                                <VAutocomplete :menu-props="{ maxHeight: 400 }" v-model="props.item.comm_calc"
                                    :items="filterSalesforces" prepend-inner-icon="tabler-man" label="정산자 선택" item-title="user_name"
                                    item-value="id" persistent-hint single-line />
                            </VCol>
                        </VRow>
                        <!-- 👉 매출미달 차감금 -->
                        <VRow class="pt-3">
                            <VCol>
                                <label>매출미달 차감금</label>
                            </VCol>
                            <VCol>
                                <VTextField type="number" v-model="props.item.under_sales_amt"
                                    prepend-inner-icon="tabler-currency-won" placeholder="매출미달 차감금 입력"
                                    persistent-placeholder />
                            </VCol>
                        </VRow>
                        <!-- 👉 개통일 -->
                        <VRow class="pt-3">
                            <VCol>
                                <label>개통일</label>
                            </VCol>
                            <VCol>
                                <AppDateTimePicker v-model="props.item.begin_dt"
                                    prepend-inner-icon="ic-baseline-calendar-today" label="개통일 입력" single-line />
                            </VCol>
                        </VRow>
                        <!-- 👉 출고일 -->
                        <VRow class="pt-3">
                            <VCol>
                                <label>출고일</label>
                            </VCol>
                            <VCol>
                                <AppDateTimePicker v-model="props.item.ship_out_dt"
                                    prepend-inner-icon="ic-baseline-calendar-today" label="출고일 입력" single-line />
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
                <VCol cols="12" :md="md">
                    <VCardItem>
                        <VCardTitle style="margin-bottom: 1em;">옵션</VCardTitle>
                        <!-- 👉 매출전표 공급자 사용 여부 -->
                        <VRow>
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
                        </VRow>
                        <!-- 👉 매출전표 판매자 사용 여부 -->
                        <VRow>
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
                        </VRow>
                        <!-- 👉 비고 -->
                        <VRow>
                            <VCol>
                                <VTextarea v-model="props.item.note" counter label="비고(명칭)" prepend-inner-icon="twemoji-spiral-notepad"/>
                            </VCol>
                        </VRow>
                        <VRow>
                            <VCol class="d-flex gap-4">
                                <VBtn type="button" style="margin-left: auto;" @click="update()">
                                    {{ props.item.id == 0 ? "추가" : "수정" }}
                                    <VIcon end icon="tabler-checkbox" />
                                </VBtn>
                                <VBtn color="secondary" variant="tonal" @click="vForm.reset()">
                                    리셋
                                    <VIcon end icon="tabler-arrow-back" />
                                </VBtn>
                            </VCol>
                        </VRow>
                    </VCardItem>
                </VCol>
            </div>
        </VForm>
    </AppCardActions>
</template>
