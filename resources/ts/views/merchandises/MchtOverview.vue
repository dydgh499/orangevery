<script lang="ts" setup>
import { requiredValidator } from '@validators'
import type { Merchandise, UnderAutoSetting } from '@/views/types'
import BooleanRadio from '@/layouts/utils/BooleanRadio.vue'
import CreateHalfVCol from '@/layouts/utils/CreateHalfVCol.vue'
import { getUserLevel, getIndexByLevel } from '@axios'
import { useSalesFilterStore, feeApplyHistoires } from '@/views/salesforces/useStore'
import FeeChangeBtn from '@/views/merchandises/FeeChangeBtn.vue'
import { useStore } from '@/views/services/pay-gateways/useStore'
import UnderAutoSettingDialog from '@/layouts/dialogs/UnderAutoSettingDialog.vue'
import RegularCreditCard from '@/views/merchandises/regular-credit-cards/RegularCreditCard.vue'
import BaseQuestionTooltip from '@/layouts/tooltips/BaseQuestionTooltip.vue'
import { tax_category_types } from '@/views/merchandises/useStore'
import corp from '@corp'


interface Props {
    item: Merchandise,
}
const props = defineProps<Props>()
const { sales, initAllSales } = useSalesFilterStore()
const { cus_filters } = useStore()
const levels = corp.pv_options.auth.levels
const fee_histories = ref(<any[]>([]))
const underAutoSetting = ref()

const hintSalesApplyFee = (sales_id: number): string => {
    if (sales_id) {
        const history = fee_histories.value.find(obj => obj.sales_id === sales_id)
        return history ? '마지막 일괄적용: ' + (history.trx_fee * 100).toFixed(3) + '%' : '';
    }
    else
        return ''
}

const setSalesUnderAutoSetting = async (my_level: number) => {
    const setSalesAutoInfo = (my_level: number, under_auto_setting: UnderAutoSetting) => {
        const sales_key = 'sales' + my_level   
        props.item[sales_key+'_id'] = under_auto_setting.sales_id
        props.item[sales_key+'_fee'] = under_auto_setting.sales_fee
    }

    const salesforce = sales[my_level].value.find(obj => obj.id === props.item['sales'+my_level+'_id'])
    if(salesforce?.under_auto_settings?.length ) {
        if(salesforce.under_auto_settings.length > 0) {
            const idx = await underAutoSetting.value.show(salesforce.under_auto_settings)
            setSalesAutoInfo(my_level, salesforce.under_auto_settings[idx])
        }
        else
            setSalesAutoInfo(my_level, salesforce.under_auto_settings[0])
    }
    else {
        // 일괄적용
        const history = fee_histories.value.find(obj => obj.sales_id === props.item['sales'+my_level+'_id'])
        if(history)
            props.item['sales'+my_level+'_fee'] = (history.trx_fee * 100).toFixed(3)
    }
}

initAllSales()
onMounted(async () => {
    fee_histories.value = await feeApplyHistoires()
})
</script>
<template>
    <VRow>
        <!-- 👉 개인정보 -->
        <VCol cols="12" md="6">
            <VCard>
                <VCardItem>
                    <VCardTitle>가맹점정보</VCardTitle>
                    <VRow class="pt-5">
                        <CreateHalfVCol :mdl="3" :mdr="5">
                            <template #name>가맹점 상호</template>
                            <template #input>
                                <VTextField v-model="props.item.mcht_name" prepend-inner-icon="tabler-building-store"
                                    placeholder="상호를 입력해주세요" persistent-placeholder :rules="[requiredValidator]" />
                            </template>
                        </CreateHalfVCol>
                        <CreateHalfVCol :mdl="3" :mdr="5">
                            <template #name>업종</template>
                            <template #input>
                                <VTextField v-model="props.item.sector" prepend-inner-icon="tabler-building-store"
                                    placeholder="업종을 입력해주세요" persistent-placeholder />
                            </template>
                        </CreateHalfVCol>
                        <!-- 👉 상위 영업점 수수료율 -->
                        <template v-for="i in 6" :key="i">
                            <VCol cols="12" v-if="levels['sales'+(6-i)+'_use'] && getUserLevel() > getIndexByLevel(6-i)">
                                <VRow>
                                    <VCol cols="12" md="3">
                                        <label>{{ levels['sales'+(6-i)+'_name'] }}/수수료율</label>
                                    </VCol>
                                    <VCol cols="12" :md="props.item.id ? 3 : 4">
                                        <VAutocomplete :menu-props="{ maxHeight: 400 }" v-model="props.item['sales'+(6-i)+'_id']"
                                            :items="sales[6-i].value"
                                            prepend-inner-icon="ph:share-network" :label="levels['sales'+(6-i)+'_name'] + '선택'"
                                            item-title="sales_name" item-value="id" persistent-hint single-line
                                            :hint="hintSalesApplyFee(props.item['sales'+(6-i)+'_id'])" @update:modelValue="setSalesUnderAutoSetting(6-i)"/>
                                            <VTooltip activator="parent" location="top" v-if="props.item['sales'+(6-i)+'_id']">
                                                {{ sales[6-i].value.find(obj => obj.id === props.item['sales'+(6-i)+'_id'])?.sales_name }}
                                            </VTooltip>
                                    </VCol>
                                    <VCol cols="12" :md="props.item.id ? 2 : 4">
                                        <VTextField v-model="props.item['sales'+(6-i)+'_fee'] " type="number" suffix="%"
                                            :rules="[requiredValidator]" />
                                    </VCol>
                                    <FeeChangeBtn v-if="props.item.id" :level=getIndexByLevel(6-i) :item="props.item">
                                    </FeeChangeBtn>
                                </VRow>
                            </VCol>
                        </template>
                        <!-- 👉 가맹점 수수료율 -->
                        <VCol cols="12">
                            <VRow>
                                <VCol cols="12" md="3">
                                    거래/유보금 수수료율
                                </VCol>
                                <VCol cols="12" :md="props.item.id ? 3 : 4">
                                    <VTextField v-model="props.item.trx_fee" type="number" suffix="%"
                                        :rules="[requiredValidator]" />
                                </VCol>
                                <VCol cols="12" :md="props.item.id ? 2 : 4">
                                    <VTextField v-model="props.item.hold_fee" type="number" suffix="%"
                                        :rules="[requiredValidator]" />
                                </VCol>
                                <FeeChangeBtn v-if="props.item.id" :level=-1 :item="props.item">
                                </FeeChangeBtn>
                            </VRow>
                        </VCol>
                        <VCol>
                            <VTextarea v-model="props.item.note" counter label="메모사항"
                                prepend-inner-icon="twemoji-spiral-notepad" maxlength="95" />
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
                                <CreateHalfVCol :mdl="5" :mdr="7">
                                    <template #name>사업자 유형</template>
                                    <template #input>
                                        <VAutocomplete :menu-props="{ maxHeight: 400 }" v-model="props.item.tax_category_type"
                                            :items="tax_category_types"
                                            prepend-inner-icon="ic-outline-business-center" label="사업자 종류" item-title="title"
                                            item-value="id" single-line/>
                                    </template>
                                </CreateHalfVCol>
                            </VRow>
                        </VCol>
                        <VCol cols="12">
                            <VRow>
                                <CreateHalfVCol :mdl="5" :mdr="7">
                                    <template #name>커스텀 필터</template>
                                    <template #input>
                                        <VAutocomplete :menu-props="{ maxHeight: 400 }" v-model="props.item.custom_id"
                                            :items="[{ id: null, type: 1, name: '사용안함' }].concat(cus_filters)"
                                            prepend-inner-icon="tabler:folder-question" label="커스텀 필터" item-title="name"
                                            item-value="id" single-line/>
                                    </template>
                                </CreateHalfVCol>
                            </VRow>
                        </VCol>
                        <VCol cols="12" v-if="corp.pv_options.paid.subsidiary_use_control">
                            <VRow>
                                <CreateHalfVCol :mdl="5" :mdr="7">
                                    <template #name>전산 사용상태</template>
                                    <template #input>
                                        <BooleanRadio :radio="props.item.enabled"
                                            @update:radio="props.item.enabled = $event">
                                            <template #true>ON</template>
                                            <template #false>OFF</template>
                                        </BooleanRadio>
                                    </template>
                                </CreateHalfVCol>
                            </VRow>
                        </VCol>
                        <VCol cols="12" v-if="corp.pv_options.paid.use_regular_card">
                            <VRow>
                                <CreateHalfVCol :mdl="5" :mdr="7">
                                    <template #name>단골고객 사용여부</template>
                                    <template #input>
                                        <BooleanRadio :radio="props.item.use_regular_card"
                                            @update:radio="props.item.use_regular_card = $event">
                                            <template #true>사용</template>
                                            <template #false>미사용</template>
                                        </BooleanRadio>
                                    </template>
                                </CreateHalfVCol>
                            </VRow>
                        </VCol>
                        <template v-if="corp.pv_options.paid.use_collect_withdraw">
                            <VCol cols="12">
                                <VRow>
                                    <CreateHalfVCol :mdl="5" :mdr="7">
                                        <template #name>모아서 출금</template>
                                        <template #input>
                                            <BooleanRadio :radio="props.item.use_collect_withdraw"
                                                @update:radio="props.item.use_collect_withdraw = $event">
                                                <template #true>사용</template>
                                                <template #false>미사용</template>
                                            </BooleanRadio>
                                        </template>
                                    </CreateHalfVCol>
                                </VRow>
                            </VCol>
                            <VCol cols="12">
                                <VRow>
                                    <CreateHalfVCol :mdl="5" :mdr="7">
                                        <template #name>
                                            <BaseQuestionTooltip :location="'top'" :text="'모아서 출금 수수료'"
                                                :content="'가맹점에서 모아서 출금시 사용됩니다.'">
                                            </BaseQuestionTooltip>
                                        </template>
                                        <template #input>
                                            <VTextField v-model="props.item.collect_withdraw_fee" type="number" suffix="₩"
                                                :rules="[requiredValidator]" />
                                        </template>
                                    </CreateHalfVCol>
                                </VRow>
                            </VCol>
                        </template>
                        <VCol cols="12" v-if="corp.pv_options.paid.use_withdraw_fee">
                            <VRow>
                                <CreateHalfVCol :mdl="5" :mdr="7">
                                    <template #name>출금 수수료</template>
                                    <template #input>
                                        <VTextField v-model="props.item.withdraw_fee" type="number" suffix="₩"
                                            :rules="[requiredValidator]" />
                                    </template>
                                </CreateHalfVCol>
                            </VRow>
                        </VCol>
                        <!-- 👉 매출전표 공급자 사용 여부 -->
                        <VCol cols="12">
                            <VRow>
                                <CreateHalfVCol :mdl="5" :mdr="7">
                                    <template #name>매출전표 공급자 정보</template>
                                    <template #input>
                                        <BooleanRadio :radio="props.item.use_saleslip_prov"
                                            @update:radio="props.item.use_saleslip_prov = $event">
                                            <template #true>PG사</template>
                                            <template #false>본사</template>
                                        </BooleanRadio>
                                    </template>
                                </CreateHalfVCol>
                            </VRow>
                        </VCol>
                        <!-- 👉 매출전표 판매자 사용 여부 -->
                        <VCol cols="12">
                            <VRow>
                                <CreateHalfVCol :mdl="5" :mdr="7">
                                    <template #name>매출전표 판매자 정보</template>
                                    <template #input>
                                        <BooleanRadio :radio="props.item.use_saleslip_sell"
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
            <br>
            <VCard v-if="props.item.use_regular_card">
                <VCardItem>
                    <VCol cols="12">
                        <VRow>
                            <RegularCreditCard :item="props.item" />
                        </VRow>
                    </VCol>
                </VCardItem>
            </VCard>
        </VCol>
        <UnderAutoSettingDialog ref="underAutoSetting"/>
    </VRow>
</template>
