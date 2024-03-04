<script lang="ts" setup>
import { settleCycles, settleDays, settleTaxTypes } from '@/views/salesforces/useStore'
import { getUserLevel, salesLevels } from '@axios'
import CreateHalfVCol from '@/layouts/utils/CreateHalfVCol.vue'
import BaseQuestionTooltip from '@/layouts/tooltips/BaseQuestionTooltip.vue'
import type { Salesforce } from '@/views/types'
import { requiredValidator, nullValidator } from '@validators'
import type { Options } from '@/views/types'
import BooleanRadio from '@/layouts/utils/BooleanRadio.vue'
import MchtBatchOverview from '@/layouts/components/batch-updaters/MchtBatchOverview.vue'
import PayModuleBatchOverview from '@/layouts/components/batch-updaters/PayModuleBatchOverview.vue'
import UnderAutoSettingCard from '@/views/salesforces/under-auto-settings/UnderAutoSettingCard.vue'
import corp from '@corp'

interface Props {
    item: Salesforce,
}
const props = defineProps<Props>()
const all_cycles = settleCycles()
const all_days = settleDays()
const tax_types = settleTaxTypes()

const mchtBatchOverview = ref()
const payModuleBatchOverview = ref()

const addAbleSalesLevels = () => {
    const levels = corp.pv_options.auth.levels
    const sales = <Options[]>([]);
    if (levels.sales0_use && getUserLevel() > 13)
        sales.push({ id: 13, title: levels.sales0_name })
    if (levels.sales1_use && getUserLevel() > 15)
        sales.push({ id: 15, title: levels.sales1_name })
    if (levels.sales2_use && getUserLevel() > 17)
        sales.push({ id: 17, title: levels.sales2_name })
    if (levels.sales3_use && getUserLevel() > 20)
        sales.push({ id: 20, title: levels.sales3_name })
    if (levels.sales4_use && getUserLevel() > 25)
        sales.push({ id: 25, title: levels.sales4_name })
    if (levels.sales5_use && getUserLevel() > 30)
        sales.push({ id: 30, title: levels.sales5_name })
    return props.item.id == 0 ? salesLevels() : sales
}

</script>
<template>
    <VRow>
        <!-- 👉 개인정보 -->
        <VCol cols="12" md="6">
            <VCard>
                <VCardItem>
                    <VCardTitle>영업점정보</VCardTitle>
                    <VRow class="pt-5">
                        <CreateHalfVCol :mdl="3" :mdr="9">
                            <template #name>영업점 상호</template>
                            <template #input>
                                <VTextField v-model="props.item.sales_name" prepend-inner-icon="tabler-building-store"
                                    placeholder="상호를 입력해주세요" persistent-placeholder :rules="[requiredValidator]" />
                            </template>
                        </CreateHalfVCol>
                        <CreateHalfVCol :mdl="3" :mdr="9" v-if="getUserLevel() >= 35">
                            <template #name>정산 세율</template>
                            <template #input>
                                <VRadioGroup v-model="props.item.settle_tax_type" inline :rules="[nullValidator]">
                                    <VRadio v-for="(tax_type, key, index) in tax_types" :value="tax_type.id" :key="index">
                                        <template #label>
                                            <span>
                                                {{ tax_type.title }}
                                            </span>
                                        </template>
                                    </VRadio>
                                </VRadioGroup>
                            </template>
                        </CreateHalfVCol>
                        <CreateHalfVCol :mdl="3" :mdr="9">
                            <template #name>
                                정산 주기
                            </template>
                            <template #input>
                                <VSelect :menu-props="{ maxHeight: 400 }" v-model="props.item.settle_cycle"
                                    :items="all_cycles" prepend-inner-icon="icon-park-outline:cycle" label="정산 주기 선택"
                                    item-title="title" item-value="id" persistent-hint single-line
                                    :rules="[nullValidator]" />
                            </template>
                        </CreateHalfVCol>
                        <CreateHalfVCol :mdl="3" :mdr="9">
                            <template #name>
                                정산 요일
                            </template>
                            <template #input>
                                <VSelect :menu-props="{ maxHeight: 400 }" v-model="props.item.settle_day" :items="all_days"
                                    prepend-inner-icon="icon-park-outline:cycle" label="정산 요일 선택" item-title="title"
                                    item-value="id" persistent-hint single-line />
                            </template>
                        </CreateHalfVCol>
                        <CreateHalfVCol :mdl="3" :mdr="9">
                            <template #name>
                                <BaseQuestionTooltip :location="'top'" :text="'등급'" :content="'영업자 등급은 수정할 수 없습니다.'">
                                </BaseQuestionTooltip>
                            </template>
                            <template #input>
                                <VSelect :menu-props="{ maxHeight: 400 }" v-model="props.item.level"
                                    :items="addAbleSalesLevels()" prepend-inner-icon="ph:share-network" label="영업자 등급 선택"
                                    item-title="title" item-value="id" persistent-hint single-line :rules="[nullValidator]"
                                    :readonly="props.item.id != 0" />
                            </template>
                        </CreateHalfVCol>
                        <template v-if="getUserLevel() >= 35">
                            <CreateHalfVCol :mdl="3" :mdr="9">
                                <template #name>화면 타입</template>
                                <template #input>
                                    <BooleanRadio :radio="props.item.view_type"
                                        @update:radio="props.item.view_type = $event">
                                        <template #true>상세보기</template>
                                        <template #false>간편보기</template>
                                    </BooleanRadio>
                                </template>
                            </CreateHalfVCol>
                            <CreateHalfVCol :mdl="3" :mdr="9">
                                <template #name>하위 가맹점 수정권한</template>
                                <template #input>
                                    <BooleanRadio :radio="props.item.is_able_modify_mcht"
                                        @update:radio="props.item.is_able_modify_mcht = $event">
                                        <template #true>가능</template>
                                        <template #false>불가능</template>
                                    </BooleanRadio>
                                </template>
                            </CreateHalfVCol>
                            <VCol>
                                <VTextarea v-model="props.item.note" counter label="메모사항"
                                    prepend-inner-icon="twemoji-spiral-notepad" maxlength="95" auto-grow />
                            </VCol>
                        </template>
                    </VRow>
                </VCardItem>
            </VCard>
            <br>
            <VCard v-if="corp.pv_options.paid.use_sales_auto_setting && getUserLevel() >= 35">
                <VCardItem>
                    <VCol cols="12">
                        <VRow>
                            <UnderAutoSettingCard :item="props.item" />
                        </VRow>
                    </VCol>
                </VCardItem>
            </VCard>
        </VCol>
        <VCol cols="12" md="6" v-if="getUserLevel() >= 35">
            <MchtBatchOverview ref="mchtBatchOverview" :selected_idxs="[]" :selected_sales_id="props.item.id" :selected_level="props.item.level"/>
            <br>
            <PayModuleBatchOverview ref="payModuleBatchOverview" :selected_idxs="[]" :selected_sales_id="props.item.id" :selected_level="props.item.level"/>
        </VCol>
    </VRow>
</template>
