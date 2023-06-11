<script lang="ts" setup>
import { requiredValidator, nullValidator } from '@validators';
import type { Merchandise, MchtOption } from '@/views/types'
import BooleanRadio from '@/layouts/utils/BooleanRadio.vue';
import CreateHalfVCol from '@/layouts/utils/CreateHalfVCol.vue';
import { useSalesFilterStore } from '@/views/salesforces/useStore'
import FeeChangeBtn from '@/views/merchandises/FeeChangeBtn.vue';
import { useStore } from '@/views/services/pay-gateways/useStore';
import corp from '@corp'

interface Props {
    item: Merchandise,
    pv_options: MchtOption,
}
const props = defineProps<Props>()
const { sales } = useSalesFilterStore()
const { cus_filters } = useStore()

const levels = corp.pv_options.auth.levels

onMounted(() => {
    props.pv_options.is_show_fee = Boolean(props.pv_options.is_show_fee)
    props.item.sales0_id = props.item.sales0_id == 0 ? null : props.item.sales0_id
    props.item.sales1_id = props.item.sales1_id == 0 ? null : props.item.sales1_id
    props.item.sales2_id = props.item.sales2_id == 0 ? null : props.item.sales2_id
    props.item.sales3_id = props.item.sales3_id == 0 ? null : props.item.sales3_id
    props.item.sales4_id = props.item.sales4_id == 0 ? null : props.item.sales4_id
    props.item.sales5_id = props.item.sales5_id == 0 ? null : props.item.sales5_id
})
</script>
<template>
    <VRow class="match-height">
        <!-- 👉 개인정보 -->
        <VCol cols="12" md="6">
            <VCard>
                <VCardItem>
                    <VCardTitle>가맹점정보</VCardTitle>
                    <VRow class="pt-5">
                        <CreateHalfVCol :mdl="3" :mdr="9">
                            <template #name>상호</template>
                            <template #input>
                                <VTextField v-model="props.item.mcht_name"
                                    prepend-inner-icon="tabler-building-store" placeholder="상호를 입력해주세요"
                                    persistent-placeholder :rules="[requiredValidator]" />
                            </template>
                        </CreateHalfVCol>
                        <CreateHalfVCol :mdl="3" :mdr="9">
                            <template #name>업종</template>
                            <template #input>
                                <VTextField v-model="props.item.sector"
                                    prepend-inner-icon="tabler-building-store" placeholder="업종을 입력해주세요"
                                    persistent-placeholder :rules="[requiredValidator]" />
                            </template>
                        </CreateHalfVCol>
                        <!-- 👉 수수료율 -->
                        <VCol cols="12">
                            <VRow no-gutters>
                                <VCol cols="12" md="3">
                                    <label>거래/유보금 수수료율</label>
                                </VCol>
                                <VCol cols="12" :md="props.item.id ? 3 : 4">
                                    <VTextField v-model="props.item.trx_fee" type="number"
                                        suffix="%" :rules="[requiredValidator]" />
                                </VCol>
                                <VCol cols="12" :md="props.item.id ? 3 : 4">
                                    <VTextField v-model="props.item.hold_fee" type="number"
                                        suffix="%" :rules="[requiredValidator]" />
                                </VCol>
                                <FeeChangeBtn v-if="props.item.id" :level=-1 :item="props.item">
                                </FeeChangeBtn>
                            </VRow>
                        </VCol>

                        <!-- 👉 영업점 수수료율 -->
                        <VCol cols="12" v-if="levels.sales5_use">
                            <VRow no-gutters>
                                <VCol cols="12" md="3">
                                    <label>{{ levels.sales5_name }}/수수료율</label>
                                </VCol>
                                <VCol cols="12" :md="props.item.id ? 3 : 4">
                                    <VAutocomplete :menu-props="{ maxHeight: 400 }" v-model="props.item.sales5_id"
                                        :items="sales[5].value" prepend-inner-icon="tabler-man" label="지사 선택"
                                        item-title="nick_name" item-value="id" />
                                </VCol>
                                <VCol cols="12" :md="props.item.id ? 3 : 4">
                                    <VTextField v-model="props.item.sales5_fee" type="number"
                                        suffix="%" :rules="[requiredValidator]" />
                                </VCol>
                                <FeeChangeBtn v-if="props.item.id" :level=5 :item="props.item">
                                </FeeChangeBtn>
                            </VRow>
                        </VCol>
                        <!-- 👉 영업점 수수료율 -->
                        <VCol cols="12" v-if="levels.sales4_use">
                            <VRow no-gutters>
                                <VCol cols="12" md="3">
                                    <label>{{ levels.sales4_name }}/수수료율</label>
                                </VCol>
                                <VCol cols="12" :md="props.item.id ? 3 : 4">
                                    <VAutocomplete :menu-props="{ maxHeight: 400 }" v-model="props.item.sales4_id"
                                        :items="sales[5].value" prepend-inner-icon="tabler-man" label="하위지사 선택"
                                        item-title="nick_name" item-value="id" />
                                </VCol>
                                <VCol cols="12" :md="props.item.id ? 3 : 4">
                                    <VTextField v-model="props.item.sales4_fee" type="number"
                                        suffix="%" :rules="[requiredValidator]" />
                                </VCol>
                                <FeeChangeBtn v-if="props.item.id" :level=4 :item="props.item">
                                </FeeChangeBtn>
                            </VRow>
                        </VCol>
                        <!-- 👉 영업점 수수료율 -->
                        <VCol cols="12"  v-if="levels.sales3_use">
                            <VRow no-gutters>
                                <VCol cols="12" md="3">
                                    <label>{{ levels.sales3_name }}/수수료율</label>
                                </VCol>
                                <VCol cols="12" :md="props.item.id ? 3 : 4">
                                    <VAutocomplete :menu-props="{ maxHeight: 400 }" v-model="props.item.sales3_id"
                                        :items="sales[5].value" prepend-inner-icon="tabler-man" label="총판 선택"
                                        item-title="nick_name" item-value="id" />
                                </VCol>
                                <VCol cols="12" :md="props.item.id ? 3 : 4">
                                    <VTextField v-model="props.item.sales3_fee" type="number"
                                        suffix="%" :rules="[requiredValidator]" />
                                </VCol>
                                <FeeChangeBtn v-if="props.item.id" :level=3 :item="props.item">
                                </FeeChangeBtn>
                            </VRow>
                        </VCol>
                        <!-- 👉 영업점 수수료율 -->
                        <VCol cols="12" v-if="levels.sales2_use">
                            <VRow no-gutters>
                                <VCol cols="12" md="3">
                                    <label>{{ levels.sales2_name }}/수수료율</label>
                                </VCol>
                                <VCol cols="12" :md="props.item.id ? 3 : 4">
                                    <VAutocomplete :menu-props="{ maxHeight: 400 }" v-model="props.item.sales2_id"
                                        :items="sales[5].value" prepend-inner-icon="tabler-man" label="하위총판 선택"
                                        item-title="nick_name" item-value="id" />
                                </VCol>
                                <VCol cols="12" :md="props.item.id ? 3 : 4">
                                    <VTextField v-model="props.item.sales2_fee" type="number"
                                        suffix="%" :rules="[requiredValidator]" />
                                </VCol>
                                <FeeChangeBtn v-if="props.item.id" :level=2 :item="props.item">
                                </FeeChangeBtn>
                            </VRow>
                        </VCol>
                        <!-- 👉 영업점 수수료율 -->
                        <VCol cols="12" v-if="levels.sales1_use">
                            <VRow no-gutters>
                                <VCol cols="12" md="3">
                                    <label>{{ levels.sales1_name }}/수수료율</label>
                                </VCol>
                                <VCol cols="12" :md="props.item.id ? 3 : 4">
                                    <VAutocomplete :menu-props="{ maxHeight: 400 }" v-model="props.item.sales1_id"
                                        :items="sales[5].value" prepend-inner-icon="tabler-man" label="대리점 선택"
                                        item-title="nick_name" item-value="id" />
                                </VCol>
                                <VCol cols="12" :md="props.item.id ? 3 : 4">
                                    <VTextField v-model="props.item.sales1_fee" type="number"
                                        suffix="%" :rules="[requiredValidator]" />
                                </VCol>
                                <FeeChangeBtn v-if="props.item.id" :level=1 :item="props.item">
                                </FeeChangeBtn>
                            </VRow>
                        </VCol>
                        <!-- 👉 영업점 수수료율 -->
                        <VCol cols="12" v-if="levels.sales0_use">
                            <VRow no-gutters>
                                <VCol cols="12" md="3">
                                    <label>{{ levels.sales0_name }}하위대리점/수수료율</label>
                                </VCol>
                                <VCol cols="12" :md="props.item.id ? 3 : 4">
                                    <VAutocomplete :menu-props="{ maxHeight: 400 }" v-model="props.item.sales0_id"
                                        :items="sales[5].value" prepend-inner-icon="tabler-man" label="하위대리점 선택"
                                        item-title="nick_name" item-value="id" />
                                </VCol>
                                <VCol cols="12" :md="props.item.id ? 3 : 4">
                                    <VTextField v-model="props.item.sales0_fee" type="number"
                                        suffix="%" :rules="[requiredValidator]" />
                                </VCol>
                                <FeeChangeBtn v-if="props.item.id" :level=0 :item="props.item">
                                </FeeChangeBtn>
                            </VRow>
                        </VCol>
                        <VDivider />
                    </VRow>
                </VCardItem>
            </VCard>
        </VCol>
        <!-- 👉 계약정보 -->
        <VCol cols="12" md="6">
            <VCard>
                <VCardItem>
                    <VCardTitle>옵션정보</VCardTitle>
                    <VRow class="pt-5">
                        <VCol cols="12">
                            <VRow no-gutters>
                                <VCol cols="12" md="3">
                                    <label>커스텀 필터</label>
                                </VCol>
                                <VCol cols="12" md="9">
                                    <VAutocomplete :menu-props="{ maxHeight: 400 }" v-model="props.item.custom_id" :items="[{id:null, name:'커스텀 필터 선택'}].concat(cus_filters)"
                                        prepend-inner-icon="tabler:folder-question" label="커스텀 필터" item-title="name" item-value="id"
                                        persistent-hint />
                                </VCol>
                            </VRow>
                        </VCol>

                        <VCol cols="12">
                            <VRow no-gutters>
                                <VCol cols="12" md="3">
                                    <label>가맹점 수수료율 노출</label>
                                </VCol>
                                <VCol cols="12" md="9">
                                    <BooleanRadio :radio.sync="props.pv_options.is_show_fee"
                                        @update:radio="props.pv_options.is_show_fee = $event">
                                        <template #true>사용</template>
                                        <template #false>미사용</template>
                                    </BooleanRadio>
                                </VCol>
                            </VRow>
                        </VCol>
                        <VCol cols="12">
                            <VRow no-gutters>
                                <VCol cols="12" md="3">
                                    <label for="acctNumHorizontalIcons">중복결제 한도</label>
                                </VCol>
                                <VCol cols="12" md="9">
                                    <VTextField prepend-inner-icon="tabler-currency-won"
                                        v-model="props.pv_options.pay_dupe_limit" type="number"
                                        :rules="[requiredValidator]" />
                                </VCol>
                            </VRow>
                        </VCol>
                        <VCol cols="12">
                            <VRow no-gutters>
                                <VCol cols="12" md="3">
                                    <label>결제 일 한도</label>
                                </VCol>
                                <VCol cols="12" md="9">
                                    <VTextField prepend-inner-icon="tabler-currency-won"
                                        v-model="props.pv_options.pay_day_limit" type="number"
                                        :rules="[requiredValidator]" />
                                </VCol>
                            </VRow>
                        </VCol>
                        <VCol cols="12">
                            <VRow no-gutters>
                                <VCol cols="12" md="3">
                                    <label>결제 월 한도</label>
                                </VCol>
                                <VCol cols="12" md="9">
                                    <VTextField prepend-inner-icon="tabler-currency-won"
                                        v-model="props.pv_options.pay_month_limit" type="number"
                                        :rules="[requiredValidator]" />
                                </VCol>
                            </VRow>
                        </VCol>
                        <VCol cols="12">
                            <VRow no-gutters>
                                <VCol cols="12" md="3">
                                    <label>결제 년 한도</label>
                                </VCol>
                                <VCol cols="12" md="9">
                                    <VTextField prepend-inner-icon="tabler-currency-won"
                                        v-model="props.pv_options.pay_year_limit" type="number"
                                        :rules="[requiredValidator]" />
                                </VCol>
                            </VRow>
                        </VCol>
                        <VCol cols="12">
                            <VRow no-gutters>
                                <VCol cols="12" md="3">
                                    <label>이상거래 금액 한도</label>
                                </VCol>
                                <VCol cols="12" md="9">
                                    <VTextField prepend-inner-icon="tabler-currency-won"
                                        v-model="props.pv_options.abnormal_trans_limit" type="number"
                                        :rules="[requiredValidator]" />
                                </VCol>
                        </VRow>
                    </VCol>
                    <VDivider />
                </VRow>
            </VCardItem>
        </VCard>
    </VCol>
</VRow></template>
