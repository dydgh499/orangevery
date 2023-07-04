<script lang="ts" setup>
import { requiredValidator, nullValidator } from '@validators'
import type { Merchandise } from '@/views/types'
import BooleanRadio from '@/layouts/utils/BooleanRadio.vue'
import CreateHalfVCol from '@/layouts/utils/CreateHalfVCol.vue'
import { useSalesFilterStore } from '@/views/salesforces/useStore'
import FeeChangeBtn from '@/views/merchandises/FeeChangeBtn.vue'
import { useStore } from '@/views/services/pay-gateways/useStore'
import BaseQuestionTooltip from '@/layouts/tooltips/BaseQuestionTooltip.vue'
import corp from '@corp'

interface Props {
    item: Merchandise,
}
const props = defineProps<Props>()
const { sales, classification } = useSalesFilterStore()
const { cus_filters } = useStore()
const levels = corp.pv_options.auth.levels

const sales5 = ref({ id: null, sales_name: '선택안함' })
const sales4 = ref({ id: null, sales_name: '선택안함' })
const sales3 = ref({ id: null, sales_name: '선택안함' })
const sales2 = ref({ id: null, sales_name: '선택안함' })
const sales1 = ref({ id: null, sales_name: '선택안함' })
const sales0 = ref({ id: null, sales_name: '선택안함' })
const custom = ref({ id: null, type: 1, name: '사용안함' })

classification()
watchEffect(() => {
    props.item.sales0_fee = props.item.sales0_fee.toFixed(3)
    props.item.sales1_fee = props.item.sales1_fee.toFixed(3)
    props.item.sales2_fee = props.item.sales2_fee.toFixed(3)
    props.item.sales3_fee = props.item.sales3_fee.toFixed(3)
    props.item.sales4_fee = props.item.sales4_fee.toFixed(3)
    props.item.sales5_fee = props.item.sales5_fee.toFixed(3)
    props.item.trx_fee = props.item.trx_fee.toFixed(3)
    props.item.hold_fee = props.item.hold_fee.toFixed(3)
})

watchEffect(() => {
    props.item.sales5_id = sales5.value.id
    props.item.sales4_id = sales4.value.id
    props.item.sales3_id = sales3.value.id
    props.item.sales2_id = sales2.value.id
    props.item.sales1_id = sales1.value.id
    props.item.sales0_id = sales0.value.id
    props.item.custom_id = custom.value.id
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
                            <template #name>가맹점 상호</template>
                            <template #input>
                                <VTextField v-model="props.item.mcht_name" prepend-inner-icon="tabler-building-store"
                                    placeholder="상호를 입력해주세요" persistent-placeholder :rules="[requiredValidator]" />
                            </template>
                        </CreateHalfVCol>
                        <CreateHalfVCol :mdl="3" :mdr="9">
                            <template #name>업종</template>
                            <template #input>
                                <VTextField v-model="props.item.sector" prepend-inner-icon="tabler-building-store"
                                    placeholder="업종을 입력해주세요" persistent-placeholder :rules="[requiredValidator]" />
                            </template>
                        </CreateHalfVCol>
                        <!-- 👉 수수료율 -->
                        <VCol cols="12">
                            <VRow>
                                <VCol cols="12" md="3">
                                    <BaseQuestionTooltip :location="'top'" :text="'거래/유보금 수수료율'"
                                        :content="'화면 해상도가 맞지않아 버튼이 보이지 않는다면 Ctrl+Mouse wheel을 통해 해상도를 조절해주세요.'">
                                    </BaseQuestionTooltip>
                                </VCol>
                                <VCol cols="12" :md="props.item.id ? 3 : 4">
                                    <VTextField v-model="props.item.trx_fee" type="number" suffix="%"
                                        :rules="[requiredValidator]" />
                                </VCol>
                                <VCol cols="12" :md="props.item.id ? 3 : 4">
                                    <VTextField v-model="props.item.hold_fee" type="number" suffix="%"
                                        :rules="[requiredValidator]" />
                                </VCol>
                                <FeeChangeBtn v-if="props.item.id" :level=-1 :item="props.item">
                                </FeeChangeBtn>
                            </VRow>
                        </VCol>
                        <!-- 👉 영업점 수수료율 -->
                        <VCol cols="12" v-if="levels.sales5_use">
                            <VRow>
                                <VCol cols="12" md="3">
                                    <label>{{ levels.sales5_name }}/수수료율</label>
                                </VCol>
                                <VCol cols="12" :md="props.item.id ? 3 : 4">
                                    <VAutocomplete :menu-props="{ maxHeight: 400 }" v-model="sales5"
                                        :items="[{ id: null, sales_name: '선택안함' }].concat(sales[5].value)"
                                        prepend-inner-icon="ph:share-network" label="지사 선택" item-title="sales_name" item-value="id"
                                        return-object />
                                </VCol>
                                <VCol cols="12" :md="props.item.id ? 3 : 4">
                                    <VTextField v-model="props.item.sales5_fee" type="number" suffix="%"
                                        :rules="[requiredValidator]" />
                                </VCol>
                                <FeeChangeBtn v-if="props.item.id" :level=5 :item="props.item">
                                </FeeChangeBtn>
                            </VRow>
                        </VCol>
                        <!-- 👉 영업점 수수료율 -->
                        <VCol cols="12" v-if="levels.sales4_use">
                            <VRow>
                                <VCol cols="12" md="3">
                                    <label>{{ levels.sales4_name }}/수수료율</label>
                                </VCol>
                                <VCol cols="12" :md="props.item.id ? 3 : 4">
                                    <VAutocomplete :menu-props="{ maxHeight: 400 }" v-model="sales4"
                                        :items="[{ id: null, sales_name: '선택안함' }].concat(sales[4].value)"
                                        prepend-inner-icon="ph:share-network" label="하위지사 선택" item-title="sales_name"
                                        item-value="id" return-object/>
                                </VCol>
                                <VCol cols="12" :md="props.item.id ? 3 : 4">
                                    <VTextField v-model="props.item.sales4_fee" type="number" suffix="%"
                                        :rules="[requiredValidator]" />
                                </VCol>
                                <FeeChangeBtn v-if="props.item.id" :level=4 :item="props.item">
                                </FeeChangeBtn>
                            </VRow>
                        </VCol>
                        <!-- 👉 영업점 수수료율 -->
                        <VCol cols="12" v-if="levels.sales3_use">
                            <VRow>
                                <VCol cols="12" md="3">
                                    <label>{{ levels.sales3_name }}/수수료율</label>
                                </VCol>
                                <VCol cols="12" :md="props.item.id ? 3 : 4">
                                    <VAutocomplete :menu-props="{ maxHeight: 400 }" v-model="sales3"
                                        :items="[{ id: null, sales_name: '선택안함' }].concat(sales[3].value)"
                                        prepend-inner-icon="ph:share-network" label="총판 선택" item-title="sales_name" item-value="id"
                                        return-object />
                                </VCol>
                                <VCol cols="12" :md="props.item.id ? 3 : 4">
                                    <VTextField v-model="props.item.sales3_fee" type="number" suffix="%"
                                        :rules="[requiredValidator]" />
                                </VCol>
                                <FeeChangeBtn v-if="props.item.id" :level=3 :item="props.item">
                                </FeeChangeBtn>
                            </VRow>
                        </VCol>
                        <!-- 👉 영업점 수수료율 -->
                        <VCol cols="12" v-if="levels.sales2_use">
                            <VRow>
                                <VCol cols="12" md="3">
                                    <label>{{ levels.sales2_name }}/수수료율</label>
                                </VCol>
                                <VCol cols="12" :md="props.item.id ? 3 : 4">
                                    <VAutocomplete :menu-props="{ maxHeight: 400 }" v-model="sales2"
                                        :items="[{ id: null, sales_name: '선택안함' }].concat(sales[2].value)"
                                        prepend-inner-icon="ph:share-network" label="하위총판 선택" item-title="sales_name"
                                        item-value="id" return-object />
                                </VCol>
                                <VCol cols="12" :md="props.item.id ? 3 : 4">
                                    <VTextField v-model="props.item.sales2_fee" type="number" suffix="%"
                                        :rules="[requiredValidator]" />
                                </VCol>
                                <FeeChangeBtn v-if="props.item.id" :level=2 :item="props.item">
                                </FeeChangeBtn>
                            </VRow>
                        </VCol>
                        <!-- 👉 영업점 수수료율 -->
                        <VCol cols="12" v-if="levels.sales1_use">
                            <VRow>
                                <VCol cols="12" md="3">
                                    <label>{{ levels.sales1_name }}/수수료율</label>
                                </VCol>
                                <VCol cols="12" :md="props.item.id ? 3 : 4">
                                    <VAutocomplete :menu-props="{ maxHeight: 400 }" v-model="sales1"
                                        :items="[{ id: null, sales_name: '선택안함' }].concat(sales[1].value)"
                                        prepend-inner-icon="ph:share-network" label="대리점 선택" item-title="sales_name"
                                        item-value="id" return-object />
                                </VCol>
                                <VCol cols="12" :md="props.item.id ? 3 : 4">
                                    <VTextField v-model="props.item.sales1_fee" type="number" suffix="%"
                                        :rules="[requiredValidator]" />
                                </VCol>
                                <FeeChangeBtn v-if="props.item.id" :level=1 :item="props.item">
                                </FeeChangeBtn>
                            </VRow>
                        </VCol>
                        <!-- 👉 영업점 수수료율 -->
                        <VCol cols="12" v-if="levels.sales0_use">
                            <VRow>
                                <VCol cols="12" md="3">
                                    <label>{{ levels.sales0_name }}/수수료율</label>
                                </VCol>
                                <VCol cols="12" :md="props.item.id ? 3 : 4">
                                    <VAutocomplete :menu-props="{ maxHeight: 400 }" v-model="sales0"
                                        :items="[{ id: null, sales_name: '선택안함' }].concat(sales[0].value)"
                                        prepend-inner-icon="ph:share-network" label="하위대리점 선택" item-title="sales_name"
                                        item-value="id" return-object />
                                </VCol>
                                <VCol cols="12" :md="props.item.id ? 3 : 4">
                                    <VTextField v-model="props.item.sales0_fee" type="number" suffix="%"
                                        :rules="[requiredValidator]" />
                                </VCol>
                                <FeeChangeBtn v-if="props.item.id" :level=0 :item="props.item">
                                </FeeChangeBtn>
                            </VRow>
                        </VCol>
                        <VCol>
                            <VTextarea v-model="props.item.note" counter label="메모사항"
                                prepend-inner-icon="twemoji-spiral-notepad"  maxlength="100"/>
                        </VCol>
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
                            <VRow>
                                <CreateHalfVCol :mdl="3" :mdr="9">
                                    <template #name>커스텀 필터</template>
                                    <template #input>
                                        <VAutocomplete :menu-props="{ maxHeight: 400 }" v-model="custom"
                                            :items="[{ id: null, type: 1, name: '사용안함' }].concat(cus_filters)"
                                            prepend-inner-icon="tabler:folder-question" label="커스텀 필터" item-title="name"
                                            item-value="id" persistent-hint return-object />
                                    </template>
                                </CreateHalfVCol>
                            </VRow>
                        </VCol>
                        <VCol cols="12" v-if="corp.pv_options.paid.subsidiary_use_control">
                            <VRow>
                                <CreateHalfVCol :mdl="3" :mdr="9">
                                    <template #name>전산 사용상태</template>
                                    <template #input>
                                        <BooleanRadio :radio="Boolean(props.item.enabled)"
                                            @update:radio="props.item.enabled = $event">
                                            <template #true>ON</template>
                                            <template #false>OFF</template>
                                        </BooleanRadio>
                                    </template>
                                </CreateHalfVCol>
                            </VRow>
                        </VCol>
                        <VCol cols="12">
                            <VRow>
                                <CreateHalfVCol :mdl="3" :mdr="9">
                                    <template #name>가맹점 수수료율 노출</template>
                                    <template #input>
                                        <BooleanRadio :radio="Boolean(props.item.is_show_fee)"
                                            @update:radio="props.item.is_show_fee = $event">
                                            <template #true>사용</template>
                                            <template #false>미사용</template>
                                        </BooleanRadio>
                                    </template>
                                </CreateHalfVCol>
                            </VRow>
                        </VCol>
                        <!-- 👉 매출전표 공급자 사용 여부 -->
                        <VCol cols="12">
                            <VRow>
                                <CreateHalfVCol :mdl="3" :mdr="9">
                                    <template #name>매출전표 공급자 정보</template>
                                    <template #input>
                                        <BooleanRadio :radio="Boolean(props.item.use_saleslip_prov)"
                                            @update:radio="props.item.use_saleslip_prov = $event">
                                            <template #true>본사</template>
                                            <template #false>가맹점</template>
                                        </BooleanRadio>
                                    </template>
                                </CreateHalfVCol>
                            </VRow>
                        </VCol>
                        <!-- 👉 매출전표 판매자 사용 여부 -->
                        <VCol cols="12">
                            <VRow>
                                <CreateHalfVCol :mdl="3" :mdr="9">
                                    <template #name>매출전표 판매자 정보</template>
                                    <template #input>
                                        <BooleanRadio :radio="Boolean(props.item.use_saleslip_sell)"
                                            @update:radio="props.item.use_saleslip_sell = $event">
                                            <template #true>본사</template>
                                            <template #false>가맹점</template>
                                        </BooleanRadio>
                                    </template>
                                </CreateHalfVCol>
                            </VRow>
                        </VCol>
                    </VRow>
                </VCardItem>
            </VCard>
        </VCol>
    </VRow>
</template>
