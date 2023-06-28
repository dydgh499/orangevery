<script lang="ts" setup>
import { requiredValidator, lengthValidatorV2 } from '@validators'
import type { Transaction, Merchandise, PayModule, PaySection, Options } from '@/views/types'
import { useSalesFilterStore } from '@/views/salesforces/useStore'
import { useStore } from '@/views/services/pay-gateways/useStore'
import corp from '@corp'
import { axios } from '@axios'
import { module_types, installments, payModFilter } from '@/views/merchandises/pay-modules/useStore'
import CreateHalfVCol from '@/layouts/utils/CreateHalfVCol.vue'
import BaseQuestionTooltip from '@/layouts/tooltips/BaseQuestionTooltip.vue'

interface Props {
    item: Transaction,
}
const initMchtPayModInfo = () => {
    axios.get('/api/v1/manager/merchandises/all')
        .then(r => { merchandises.value = r.data.content as Merchandise[] })
        .catch(e => { snackbar.value.show(e.response.data.message, 'error') })

    axios.get('/api/v1/manager/merchandises/pay-modules/all')
        .then(r => { pay_modules.value = r.data.content as PayModule[] })
        .catch(e => { snackbar.value.show(e.response.data.message, 'error') })
}

const props = defineProps<Props>()
const snackbar = <any>(inject('snackbar'))
const { sales } = useSalesFilterStore()
const { pgs, pss, settle_types, terminals, cus_filters, psFilter } = useStore()

const levels = corp.pv_options.auth.levels
const merchandises = ref<Merchandise[]>([])
const pay_modules = ref<PayModule[]>([])
initMchtPayModInfo()


const filterPgs = computed(() => {
    const filter = pss.filter(item => { return item.pg_id == props.item.pg_id })
    props.item.ps_id = psFilter(filter, props.item.ps_id as number)
    props.item.ps_fee = pss.find((obj: PaySection) => obj.id == props.item.ps_id)?.trx_fee
    return filter
})
const filterPayMod = computed(() => {
    const filter = pay_modules.value.filter((obj: PayModule) => { return obj.mcht_id == props.item.mcht_id })
    props.item.pmod_id = payModFilter(pay_modules.value, filter, props.item.pmod_id as number)
    return filter
})
const filterInsts = computed(() => {
    if (props.item.pmod_id != null) {
        const pmod = pay_modules.value.find((obj: PayModule) => obj.id == props.item.pmod_id)
        return installments.filter((obj: Options) => { return pmod && obj.id <= pmod.installment });
    }
    else
        return []
})
const initTrxAt = (is_trx: boolean) => {
    if (is_trx) {
        props.item.trx_dt = null
        props.item.trx_tm = null
    }
    else {
        props.item.cxl_dt = null
        props.item.cxl_tm = null
    }
}
const changePaymodEvent = () => {
    if (props.item.pmod_id != null) {
        const pmod = pay_modules.value.find((obj: PayModule) => obj.id == props.item.pmod_id)
        if (pmod) {
            props.item.module_type = pmod.module_type
            props.item.terminal_id = pmod.terminal_id
            props.item.pg_id = pmod.pg_id
            props.item.ps_id = pmod.ps_id
            props.item.mcht_settle_type = pmod.settle_type
            props.item.mcht_settle_fee = pmod.settle_fee as number
            props.item.mid = pmod.mid
            props.item.tid = pmod.tid
        }
    }
}
const changeMchtEvent = () => {
    if (props.item.mcht_id != null) {
        const mcht = merchandises.value.find((obj: Merchandise) => obj.id == props.item.mcht_id)
        if (mcht) {
            props.item.sales5_fee = mcht.sales5_fee
            props.item.sales4_fee = mcht.sales4_fee
            props.item.sales3_fee = mcht.sales3_fee
            props.item.sales2_fee = mcht.sales2_fee
            props.item.sales1_fee = mcht.sales1_fee
            props.item.sales0_fee = mcht.sales0_fee
            props.item.hold_fee = mcht.hold_fee
            props.item.mcht_fee = mcht.trx_fee

            props.item.sales5_id = mcht.sales5_id
            props.item.sales4_id = mcht.sales4_id
            props.item.sales3_id = mcht.sales3_id
            props.item.sales2_id = mcht.sales2_id
            props.item.sales1_id = mcht.sales1_id
            props.item.sales0_id = mcht.sales0_id
            props.item.custom_id = mcht.custom_id
        }
    }
}

</script>
<template>
    <VRow class="match-height">
        <!-- 👉 가맹점 정보 -->
        <VCol cols="12" md="4">
            <VCard>
                <VCardItem>
                    <VCardTitle>가맹점 정보</VCardTitle>
                    <VRow class="pt-5">
                        <!-- 👉 영업점 수수료율 -->
                        <VCol cols="12" v-if="levels.sales5_use">
                            <VRow no-gutters>
                                <VCol cols="12" md="4">
                                    <label>{{ levels.sales5_name }}/수수료율</label>
                                </VCol>
                                <VCol cols="12" :md="4">
                                    <VAutocomplete :menu-props="{ maxHeight: 400 }" v-model="props.item.sales5_id"
                                        :items="[{ id: null, user_name: '선택안함' }].concat(sales[5].value)"
                                        prepend-inner-icon="tabler-man" label="지사 선택" item-title="user_name" item-value="id"
                                        create />
                                </VCol>
                                <VCol cols="12" :md="4">
                                    <VTextField v-model="props.item.sales5_fee" type="number" suffix="%"
                                        :rules="[requiredValidator]" />
                                </VCol>
                            </VRow>
                        </VCol>
                        <!-- 👉 영업점 수수료율 -->
                        <VCol cols="12" v-if="levels.sales4_use">
                            <VRow no-gutters>
                                <VCol cols="12" md="4">
                                    <label>{{ levels.sales4_name }}/수수료율</label>
                                </VCol>
                                <VCol cols="12" :md="4">
                                    <VAutocomplete :menu-props="{ maxHeight: 400 }" v-model="props.item.sales4_id"
                                        :items="[{ id: null, user_name: '선택안함' }].concat(sales[4].value)"
                                        prepend-inner-icon="tabler-man" label="하위지사 선택" item-title="user_name"
                                        item-value="id" create />
                                </VCol>
                                <VCol cols="12" :md="4">
                                    <VTextField v-model="props.item.sales4_fee" type="number" suffix="%"
                                        :rules="[requiredValidator]" />
                                </VCol>
                            </VRow>
                        </VCol>
                        <!-- 👉 영업점 수수료율 -->
                        <VCol cols="12" v-if="levels.sales3_use">
                            <VRow no-gutters>
                                <VCol cols="12" md="4">
                                    <label>{{ levels.sales3_name }}/수수료율</label>
                                </VCol>
                                <VCol cols="12" :md="4">
                                    <VAutocomplete :menu-props="{ maxHeight: 400 }" v-model="props.item.sales3_id"
                                        :items="[{ id: null, user_name: '선택안함' }].concat(sales[3].value)"
                                        prepend-inner-icon="tabler-man" label="총판 선택" item-title="user_name" item-value="id"
                                        create />
                                </VCol>
                                <VCol cols="12" :md="4">
                                    <VTextField v-model="props.item.sales3_fee" type="number" suffix="%"
                                        :rules="[requiredValidator]" />
                                </VCol>
                            </VRow>
                        </VCol>
                        <!-- 👉 영업점 수수료율 -->
                        <VCol cols="12" v-if="levels.sales2_use">
                            <VRow no-gutters>
                                <VCol cols="12" md="4">
                                    <label>{{ levels.sales2_name }}/수수료율</label>
                                </VCol>
                                <VCol cols="12" :md="4">
                                    <VAutocomplete :menu-props="{ maxHeight: 400 }" v-model="props.item.sales2_id"
                                        :items="[{ id: null, user_name: '선택안함' }].concat(sales[2].value)"
                                        prepend-inner-icon="tabler-man" label="하위총판 선택" item-title="user_name"
                                        item-value="id" create />
                                </VCol>
                                <VCol cols="12" :md="4">
                                    <VTextField v-model="props.item.sales2_fee" type="number" suffix="%"
                                        :rules="[requiredValidator]" />
                                </VCol>
                            </VRow>
                        </VCol>
                        <!-- 👉 영업점 수수료율 -->
                        <VCol cols="12" v-if="levels.sales1_use">
                            <VRow no-gutters>
                                <VCol cols="12" md="4">
                                    <label>{{ levels.sales1_name }}/수수료율</label>
                                </VCol>
                                <VCol cols="12" :md="4">
                                    <VAutocomplete :menu-props="{ maxHeight: 400 }" v-model="props.item.sales1_id"
                                        :items="[{ id: null, user_name: '선택안함' }].concat(sales[1].value)"
                                        prepend-inner-icon="tabler-man" label="대리점 선택" item-title="user_name"
                                        item-value="id" create />
                                </VCol>
                                <VCol cols="12" :md="4">
                                    <VTextField v-model="props.item.sales1_fee" type="number" suffix="%"
                                        :rules="[requiredValidator]" />
                                </VCol>
                            </VRow>
                        </VCol>
                        <!-- 👉 영업점 수수료율 -->
                        <VCol cols="12" v-if="levels.sales0_use">
                            <VRow no-gutters>
                                <VCol cols="12" md="4">
                                    <label>{{ levels.sales0_name }}/수수료율</label>
                                </VCol>
                                <VCol cols="12" :md="4">
                                    <VAutocomplete :menu-props="{ maxHeight: 400 }" v-model="props.item.sales0_id"
                                        :items="[{ id: null, user_name: '선택안함' }].concat(sales[0].value)"
                                        prepend-inner-icon="tabler-man" label="하위대리점 선택" item-title="user_name"
                                        item-value="id" create />
                                </VCol>
                                <VCol cols="12" :md="4">
                                    <VTextField v-model="props.item.sales0_fee" type="number" suffix="%"
                                        :rules="[requiredValidator]" />
                                </VCol>
                            </VRow>
                        </VCol>
                        <!-- 👉 가맹점 수수료율 -->
                        <VCol cols="12">
                            <VRow no-gutters>
                                <VCol cols="12" md="4">
                                    <BaseQuestionTooltip :location="'top'" :text="'가맹점/수수료율'"
                                        :content="'가맹점 선택시 가맹점 정보 및 결제모듈 선택란이 현재 설정값 기준으로 세팅됩니다.<br>수수료율을 주의해서 입력해주시길 부탁드립니다.'">
                                    </BaseQuestionTooltip>
                                </VCol>
                                <VCol cols="12" :md="4">
                                    <VAutocomplete :menu-props="{ maxHeight: 400 }" v-model="props.item.mcht_id"
                                        :items="[{ id: null, mcht_name: '선택안함' }].concat(merchandises)"
                                        prepend-inner-icon="tabler-man" label="가맹점 선택" item-title="mcht_name"
                                        item-value="id" @update:modelValue="changeMchtEvent()" create />
                                </VCol>
                                <VCol cols="12" :md="4">
                                    <VTextField v-model="props.item.mcht_fee" type="number" suffix="%"
                                        :rules="[requiredValidator]" />
                                </VCol>
                            </VRow>
                        </VCol>
                        <VCol cols="12">
                            <VRow no-gutters>
                                <VCol cols="12" md="4">
                                    <label>유보금 수수료율</label>
                                </VCol>
                                <VCol cols="12" :md="8">
                                    <VTextField v-model="props.item.hold_fee" type="number" suffix="%"
                                        :rules="[requiredValidator]" />
                                </VCol>
                            </VRow>
                        </VCol>
                        <VCol cols="12">
                            <VRow no-gutters>
                                <VCol cols="12" md="4">
                                    <label>커스텀 필터</label>
                                </VCol>
                                <VCol cols="12" md="8">
                                    <VAutocomplete :menu-props="{ maxHeight: 400 }" v-model="props.item.custom_id"
                                        :items="[{ id: null, name: '선택안함' }].concat(cus_filters)"
                                        prepend-inner-icon="tabler:folder-question" label="커스텀 필터" item-title="name"
                                        item-value="id" persistent-hint create />
                                </VCol>
                            </VRow>
                        </VCol>
                        <VDivider />
                    </VRow>
                </VCardItem>
            </VCard>
        </VCol>
        <!-- 👉 결제모듈 정보 -->
        <VCol cols="12" md="4">
            <VCard>
                <VCardItem>
                    <VCardTitle>결제모듈 정보</VCardTitle>
                    <VRow class="pt-5">
                        <VCol cols="12">
                            <VRow no-gutters>
                                <CreateHalfVCol :mdl="4" :mdr="8">
                                    <template #name>
                                        <BaseQuestionTooltip :location="'top'" :text="'결제모듈 선택'"
                                            :content="'결제모듈 선택 시 결제모듈 정보란이 현시각 기준 결제모듈 설정값으로 자동 세팅됩니다.<br>선택란이 나오지 않는다면, 가맹점을 먼저 선택해주세요.'">
                                        </BaseQuestionTooltip>
                                    </template>
                                    <template #input>
                                        <VSelect :menu-props="{ maxHeight: 400 }" v-model="props.item.pmod_id"
                                            :items="filterPayMod" prepend-inner-icon="ic-outline-send-to-mobile"
                                            label="결제모듈 선택" item-title="note" item-value="id" single-line
                                            :rules=[requiredValidator] @update:modelValue="changePaymodEvent()" />
                                    </template>
                                </CreateHalfVCol>
                            </VRow>
                        </VCol>
                        <VCol cols="12">
                            <VRow no-gutters>
                                <CreateHalfVCol :mdl="4" :mdr="8">
                                    <template #name>결제모듈 타입</template>
                                    <template #input>
                                        <VSelect :menu-props="{ maxHeight: 400 }" v-model="props.item.module_type"
                                            :items="module_types" prepend-inner-icon="ic-outline-send-to-mobile"
                                            label="결제모듈 타입 선택" item-title="title" item-value="id" single-line
                                            :rules=[requiredValidator] />
                                    </template>
                                </CreateHalfVCol>
                            </VRow>
                        </VCol>
                        <VCol cols="12" v-show="props.item.module_type == 0">
                            <VRow no-gutters>
                                <CreateHalfVCol :mdl="4" :mdr="8">
                                    <template #name>단말기 타입</template>
                                    <template #input>
                                        <VSelect :menu-props="{ maxHeight: 400 }" v-model="props.item.terminal_id"
                                            :items="terminals" prepend-inner-icon="ic-outline-send-to-mobile" label="단말기 선택"
                                            item-title="name" item-value="id" single-line :rules=[requiredValidator] />
                                    </template>
                                </CreateHalfVCol>
                            </VRow>
                        </VCol>
                        <VCol cols="12">
                            <VRow no-gutters>
                                <CreateHalfVCol :mdl="4" :mdr="8">
                                    <template #name>PG사</template>
                                    <template #input>
                                        <VSelect :menu-props="{ maxHeight: 400 }" v-model="props.item.pg_id" :items="pgs"
                                            prepend-inner-icon="ph-buildings" label="PG사 선택" item-title="pg_nm"
                                            item-value="id" single-line :rules=[requiredValidator] />
                                    </template>
                                </CreateHalfVCol>
                            </VRow>
                        </VCol>
                        <!-- 👉 PG 구간 -->
                        <VCol cols="12">
                            <VRow no-gutters>
                                <CreateHalfVCol :mdl="4" :mdr="8">
                                    <template #name>구간</template>
                                    <template #input>
                                        <VSelect :menu-props="{ maxHeight: 400 }" v-model="props.item.ps_id"
                                            :items="filterPgs" prepend-inner-icon="mdi-vector-intersection" label="구간 선택"
                                            item-title="name" item-value="id" single-line :rules=[requiredValidator] />
                                    </template>
                                </CreateHalfVCol>
                            </VRow>
                        </VCol>
                        <!-- 👉 PG 수수료 -->
                        <VCol cols="12">
                            <VRow no-gutters>
                                <CreateHalfVCol :mdl="4" :mdr="8">
                                    <template #name>구간 수수료</template>
                                    <template #input>
                                        <VTextField v-model="props.item.ps_fee" type="number" suffix="%"
                                            :rules="[requiredValidator]" />
                                    </template>
                                </CreateHalfVCol>
                            </VRow>
                        </VCol>
                        <!-- 👉 정산일 -->
                        <VCol cols="12">
                            <VRow no-gutters>
                                <CreateHalfVCol :mdl="4" :mdr="8">
                                    <template #name>정산일</template>
                                    <template #input>
                                        <VSelect :menu-props="{ maxHeight: 400 }" v-model="props.item.mcht_settle_type"
                                            :items="settle_types" prepend-inner-icon="ic-outline-send-to-mobile"
                                            label="정산일 선택" item-title="name" item-value="id" :rules=[requiredValidator] />
                                    </template>
                                </CreateHalfVCol>
                            </VRow>
                        </VCol>
                        <VCol cols="12">
                            <VRow no-gutters>
                                <CreateHalfVCol :mdl="4" :mdr="8">
                                    <template #name>입금 수수료</template>
                                    <template #input>
                                        <VTextField v-model="props.item.mcht_settle_fee" type="number" suffix="￦"
                                            :rules="[requiredValidator]" />
                                    </template>
                                </CreateHalfVCol>
                            </VRow>
                        </VCol>
                        <VCol cols="12">
                            <VRow no-gutters>
                                <CreateHalfVCol :mdl="4" :mdr="8">
                                    <template #name>MID</template>
                                    <template #input>
                                        <VTextField v-model="props.item.mid" type="text" :rules="[requiredValidator]"
                                            prepend-inner-icon="tabler-user" />
                                    </template>
                                </CreateHalfVCol>
                            </VRow>
                        </VCol>
                        <VCol cols="12">
                            <VRow no-gutters>
                                <CreateHalfVCol :mdl="4" :mdr="8">
                                    <template #name>TID</template>
                                    <template #input>
                                        <VTextField v-model="props.item.tid" type="text" :rules="[requiredValidator]"
                                            prepend-inner-icon="jam-key-f" />
                                    </template>
                                </CreateHalfVCol>
                            </VRow>
                        </VCol>
                    </VRow>
                </VCardItem>
            </VCard>

        </VCol>
        <!-- 👉 매출 정보 -->
        <VCol cols="12" md="4">
            <VCard>
                <VCardItem>
                    <VCardTitle>매출 정보</VCardTitle>
                    <VRow class="pt-5">
                        <VCol cols="12">
                            <VRow no-gutters>
                                <VCol cols="12" md="4">
                                    <label>거래시간</label>
                                </VCol>
                                <VCol cols="12" md="3">
                                    <VTextField v-model="props.item.trx_dt" type="date" :rules="[requiredValidator]" />
                                </VCol>
                                <VCol cols="12" md="3">
                                    <VTextField v-model="props.item.trx_tm" type="time" :rules="[requiredValidator]"
                                        step="1" />
                                </VCol>
                                <VCol cols="12" md="2" style="text-align: center;">
                                    <VBtn size="small" variant="tonal" @click="initTrxAt(true)">
                                        초기화
                                        <VIcon end
                                            icon="streamline:interface-time-rewind-back-return-clock-timer-countdown" />
                                    </VBtn>
                                </VCol>
                            </VRow>
                        </VCol>
                        <VCol cols="12">
                            <VRow no-gutters>
                                <VCol cols="12" md="4">
                                    <label>취소시간</label>
                                </VCol>
                                <VCol cols="12" md="3">
                                    <VTextField v-model="props.item.cxl_dt" type="date" />
                                </VCol>
                                <VCol cols="12" md="3">
                                    <VTextField v-model="props.item.cxl_tm" type="time" step="1" />
                                </VCol>
                                <VCol cols="12" md="2" style="text-align: center;">
                                    <VBtn size="small" variant="tonal" @click="initTrxAt(false)">
                                        초기화
                                        <VIcon end
                                            icon="streamline:interface-time-rewind-back-return-clock-timer-countdown" />
                                    </VBtn>
                                </VCol>
                            </VRow>
                        </VCol>

                        <VCol cols="12">
                            <VRow no-gutters>
                                <CreateHalfVCol :mdl="4" :mdr="8">
                                    <template #name>
                                        <BaseQuestionTooltip :location="'top'" :text="'할부'"
                                            :content="'결제모듈에서 선택한 할부설정기간 이내의 값만 할부를 선택할 수 있습니다.'">
                                        </BaseQuestionTooltip>
                                    </template>
                                    <template #input>
                                        <VSelect :menu-props="{ maxHeight: 400 }" v-model="props.item.installment"
                                            :items="filterInsts" prepend-inneer-icon="fluent-credit-card-clock-20-regular"
                                            label="할부 선택" item-title="title" item-value="id" single-line />
                                    </template>
                                </CreateHalfVCol>
                            </VRow>
                        </VCol>
                        <VCol cols="12">
                            <VRow no-gutters>
                                <CreateHalfVCol :mdl="4" :mdr="8">
                                    <template #name>
                                        <BaseQuestionTooltip :location="'top'" :text="'거래금액'"
                                            :content="'취소금액 입력시 꼭 -(마이너스 기호)를 입력해주세요.'">
                                        </BaseQuestionTooltip>
                                    </template>
                                    <template #input>
                                        <VTextField v-model="props.item.amount" type="number" suffix="￦"
                                            placeholder="거래금액을 입력해주세요" prepend-inner-icon="ic:outline-price-change"
                                            :rules="[requiredValidator]" />
                                    </template>
                                </CreateHalfVCol>
                            </VRow>
                        </VCol>
                        <VCol cols="12">
                            <VRow no-gutters>
                                <CreateHalfVCol :mdl="4" :mdr="8">
                                    <template #name>주문번호</template>
                                    <template #input>
                                        <VTextField v-model="props.item.ord_num" type="text" placeholder="주문번호를 입력해주세요"
                                            prepend-inner-icon="ic:outline-border-color" :rules="[requiredValidator]" />
                                    </template>
                                </CreateHalfVCol>
                            </VRow>
                        </VCol>
                        <VCol cols="12">
                            <VRow no-gutters>
                                <CreateHalfVCol :mdl="4" :mdr="8">
                                    <template #name>거래번호</template>
                                    <template #input>
                                        <VTextField v-model="props.item.trx_id" type="text" placeholder="거래번호를 입력해주세요"
                                            prepend-inner-icon="icon-park-twotone:transaction-order"
                                            :rules="[requiredValidator]" />
                                    </template>
                                </CreateHalfVCol>
                            </VRow>
                        </VCol>
                        <VCol cols="12">
                            <VRow no-gutters>
                                <CreateHalfVCol :mdl="4" :mdr="8">
                                    <template #name>원거래번호</template>
                                    <template #input>
                                        <VTextField v-model="props.item.ori_trx_id" type="text" placeholder="원거래번호를 입력해주세요"
                                            prepend-inner-icon="icon-park-outline:transaction-order" />
                                    </template>
                                </CreateHalfVCol>
                            </VRow>
                        </VCol>
                        <VCol cols="12">
                            <VRow no-gutters>
                                <VRow no-gutters>
                                    <CreateHalfVCol :mdl="4" :mdr="8">
                                        <template #name>카드번호</template>
                                        <template #input>
                                            <VTextField v-model="props.item.card_num" type="text" placeholder="카드번호를 입력해주세요"
                                                persistent-placeholder counter prepend-inner-icon="emojione:credit-card"
                                                :rules="[requiredValidator, lengthValidatorV2(props.item.card_num, 16)]"
                                                maxlength="16" />
                                        </template>
                                    </CreateHalfVCol>
                                </VRow>
                            </VRow>
                        </VCol>
                        <VCol cols="12">
                            <VRow no-gutters>
                                <VRow no-gutters>
                                    <CreateHalfVCol :mdl="4" :mdr="8">
                                        <template #name>발급사</template>
                                        <template #input>
                                            <VTextField v-model="props.item.issuer" type="text" placeholder="발급사를 입력해주세요"
                                                prepend-inner-icon="ph-buildings" :rules="[requiredValidator]" />
                                        </template>
                                    </CreateHalfVCol>
                                </VRow>
                            </VRow>
                        </VCol>
                        <VCol cols="12">
                            <VRow no-gutters>
                                <VRow no-gutters>
                                    <CreateHalfVCol :mdl="4" :mdr="8">
                                        <template #name>매입사</template>
                                        <template #input>
                                            <VTextField v-model="props.item.acquirer" type="text" placeholder="매입사를 입력해주세요"
                                                prepend-inner-icon="ph-buildings" :rules="[requiredValidator]" />
                                        </template>
                                    </CreateHalfVCol>
                                </VRow>
                            </VRow>
                        </VCol>
                        <VCol cols="12">
                            <VRow no-gutters>
                                <VRow no-gutters>
                                    <CreateHalfVCol :mdl="4" :mdr="8">
                                        <template #name>승인번호</template>
                                        <template #input>
                                            <VTextField v-model="props.item.appr_num" type="text" placeholder="승인번호를 입력해주세요"
                                                prepend-inner-icon="icon-park-solid:transaction-order"
                                                persistent-placeholder counter
                                                :rules="[requiredValidator, lengthValidatorV2(props.item.appr_num, 8)]"
                                                maxlength="8" />
                                        </template>
                                    </CreateHalfVCol>
                                </VRow>
                            </VRow>
                        </VCol>
                        <VCol cols="12">
                            <VRow no-gutters>
                                <VRow no-gutters>
                                    <CreateHalfVCol :mdl="4" :mdr="8">
                                        <template #name>구매자명</template>
                                        <template #input>
                                            <VTextField v-model="props.item.buyer_name" type="text"
                                                placeholder="구매자명을 입력해주세요" prepend-inner-icon="tabler-user" />
                                        </template>
                                    </CreateHalfVCol>
                                </VRow>
                            </VRow>
                        </VCol>
                        <VCol cols="12">
                            <VRow no-gutters>
                                <VRow no-gutters>
                                    <CreateHalfVCol :mdl="4" :mdr="8">
                                        <template #name>구매자 연락처</template>
                                        <template #input>
                                            <VTextField v-model="props.item.buyer_phone" type="text"
                                                placeholder="구매자 연락처를 입력해주세요" prepend-inner-icon="tabler-device-mobile" />
                                        </template>
                                    </CreateHalfVCol>
                                </VRow>
                            </VRow>
                        </VCol>
                        <VCol cols="12">
                            <VRow no-gutters>
                                <VRow no-gutters>
                                    <CreateHalfVCol :mdl="4" :mdr="8">
                                        <template #name>상품명</template>
                                        <template #input>
                                            <VTextField v-model="props.item.item_name" type="text" placeholder="상품명을 입력해주세요"
                                                prepend-inner-icon="streamline:shopping-bag-hand-bag-2-shopping-bag-purse-goods-item-products" />
                                        </template>
                                    </CreateHalfVCol>
                                </VRow>
                            </VRow>
                        </VCol>
                        <VDivider />
                    </VRow>
                </VCardItem>
            </VCard>
        </VCol>
    </VRow>
</template>
