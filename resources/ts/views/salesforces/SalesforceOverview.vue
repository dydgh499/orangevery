<script lang="ts" setup>
import MchtBatchOverview from '@/layouts/components/batch-updaters/MchtBatchOverview.vue'
import PayModuleBatchOverview from '@/layouts/components/batch-updaters/PayModuleBatchOverview.vue'
import BaseQuestionTooltip from '@/layouts/tooltips/BaseQuestionTooltip.vue'
import BooleanRadio from '@/layouts/utils/BooleanRadio.vue'
import UnderAutoSettingCard from '@/views/salesforces/under-auto-settings/UnderAutoSettingCard.vue'
import { settleCycles, settleDays, settleTaxTypes } from '@/views/salesforces/useStore'
import type { Salesforce } from '@/views/types'

import { autoUpdateSalesforceInfo } from '@/plugins/fixplus'
import { useSalesFilterStore } from '@/views/salesforces/useStore'
import { getLevelByIndex, getUserLevel, isAbleModiy, salesLevels } from '@axios'
import corp from '@corp'
import { requiredValidatorV2 } from '@validators'

interface Props {
    item: Salesforce,
}
const props = defineProps<Props>()

const { sales } = useSalesFilterStore()
const all_cycles = settleCycles()
const all_days = settleDays()
const tax_types = settleTaxTypes()

const mchtBatchOverview = ref()
const payModuleBatchOverview = ref()

const getParentSales = computed(()  => {
    const idx = getLevelByIndex(props.item.level)
    if(idx < 5) {
        return sales[idx+1].value
    }
    else
        return []
})

watchEffect(() => {
    if(corp.id === 30 && props.item.id === 0) 
        autoUpdateSalesforceInfo(props.item)
})
</script>
<template>
    <VRow>
        <!-- 👉 개인정보 -->
        <VCol cols="12" md="6">
            <VCard>
                <VCardItem>
                    <VCardTitle>영업점정보</VCardTitle>
                    <VRow class="pt-5">
                        <VCol cols="12">
                            <VRow>
                                <VCol cols="12" md="6">
                                    <VRow no-gutters style="align-items: center;" v-if="isAbleModiy(props.item.id)">
                                        <VCol>* 영업점 상호</VCol>
                                        <VCol md="8">
                                            <VTextField v-model="props.item.sales_name" prepend-inner-icon="tabler-building-store"
                                                placeholder="상호를 입력해주세요" persistent-placeholder :rules="[requiredValidatorV2(props.item.sales_name, '영업점 상호')]" />
                                        </VCol>
                                    </VRow>
                                    <VRow v-else>
                                        <VCol class="font-weight-bold">영업점 상호</VCol>
                                        <VCol md="8"><span>{{ props.item.sales_name }}</span></VCol>
                                    </VRow>
                                </VCol>
                            </VRow>
                        </VCol>
                        <VCol cols="12" v-if="corp.id !== 30">
                            <VRow>
                                <VCol cols="12" md="6">
                                    <VRow no-gutters style="align-items: center;" v-if="isAbleModiy(props.item.id)">
                                        <VCol>정산 주기</VCol>
                                        <VCol md="8">
                                            <VSelect :menu-props="{ maxHeight: 400 }" v-model="props.item.settle_cycle"
                                                :items="all_cycles" prepend-inner-icon="icon-park-outline:cycle" label="정산 주기 선택"
                                                item-title="title" item-value="id" persistent-hint single-line
                                                :rules="[requiredValidatorV2(props.item.settle_cycle, '정산 주기')]" />
                                        </VCol>
                                    </VRow>
                                    <VRow v-else>
                                        <VCol class="font-weight-bold">정산 주기</VCol>
                                        <VCol md="8"><span>{{ all_cycles.find(obj => obj.id === props.item.settle_cycle).title }}</span></VCol>
                                    </VRow>
                                </VCol>
                                <VCol cols="12" md="6">
                                    <VRow no-gutters style="align-items: center;" v-if="isAbleModiy(props.item.id)">
                                        <VCol>정산 요일</VCol>
                                        <VCol md="8"> 
                                            <VSelect :menu-props="{ maxHeight: 400 }" v-model="props.item.settle_day" :items="all_days"
                                                prepend-inner-icon="icon-park-outline:cycle" label="정산 요일 선택" item-title="title"
                                                item-value="id" persistent-hint single-line />
                                        </VCol>
                                    </VRow>
                                    <VRow v-else>
                                        <VCol class="font-weight-bold">정산 요일</VCol>
                                        <VCol md="8"><span>{{ all_days.find(obj => obj.id === props.item.settle_day).title }}</span></VCol>
                                    </VRow>
                                </VCol>
                            </VRow>
                        </VCol>
                        <VCol cols="12">
                            <VRow>
                                <VCol cols="12" md="6"  v-if="corp.id !== 30">
                                    <VRow no-gutters style="align-items: center;" v-if="isAbleModiy(props.item.id)">
                                        <VCol>정산 세율</VCol>
                                        <VCol md="8">
                                            <VRadioGroup v-model="props.item.settle_tax_type" inline :rules="[requiredValidatorV2(props.item.settle_tax_type, '정산 세율')]">
                                                <VRadio v-for="(tax_type, key, index) in tax_types" :value="tax_type.id" :key="index">
                                                    <template #label>
                                                        <span>
                                                            {{ tax_type.title }}
                                                        </span>
                                                    </template>
                                                </VRadio>
                                            </VRadioGroup>
                                        </VCol>
                                    </VRow>
                                    <VRow v-else>
                                        <VCol class="font-weight-bold">정산 세율</VCol>
                                        <VCol md="8"><span>{{ tax_types.find(obj => obj.id === props.item.settle_tax_type).title }}</span></VCol>
                                    </VRow>
                                </VCol>
                                <VCol cols="12" md="6">
                                    <VRow no-gutters style="align-items: center;" v-if="isAbleModiy(props.item.id)">
                                        <VCol>
                                            <BaseQuestionTooltip :location="'top'" :text="'등급'" :content="'영업자 등급은 수정할 수 없습니다.'">
                                            </BaseQuestionTooltip>
                                        </VCol>
                                        <VCol md="8">                                             
                                            <VSelect :menu-props="{ maxHeight: 400 }" v-model="props.item.level"
                                                :items="salesLevels()" prepend-inner-icon="ph:share-network" label="영업자 등급 선택"
                                                item-title="title" item-value="id" persistent-hint single-line :rules="[requiredValidatorV2(props.item.level, '영업자 등급')]"
                                                :readonly="props.item.id != 0" />
                                        </VCol>
                                    </VRow>
                                    <VRow v-else>
                                        <VCol class="font-weight-bold">등급</VCol>
                                        <VCol md="8"><span>{{ salesLevels().find(obj => obj.id === props.item.level).title }}</span></VCol>
                                    </VRow>
                                </VCol>
                            </VRow>
                        </VCol>
                        
                        <VCol cols="12" v-if="isAbleModiy(props.item.id) && corp.pv_options.paid.sales_parent_structure">
                            <VRow>
                                <VCol cols="12" md="6">
                                    <VRow no-gutters style="align-items: center;">
                                        <VCol>기본 수수료</VCol>
                                        <VCol md="8">
                                            <VTextField v-model="props.item.sales_fee" type="number" suffix="%" :rules="[requiredValidatorV2(props.item.sales_fee, '기본 수수료')]"/>
                                        </VCol>
                                    </VRow>
                                </VCol>
                                <VCol cols="12" md="6">
                                    <VRow no-gutters style="align-items: center;">
                                        <VCol>상위 영업점</VCol>
                                        <VCol md="8">
                                            <VAutocomplete :menu-props="{ maxHeight: 400 }" v-model="props.item.parent_id"
                                                :items="getParentSales"
                                                :label="'상위영업점 선택'"
                                                item-title="sales_name" item-value="id" persistent-hint single-line prepend-inner-icon="ph:share-network" 
                                                :rules="props.item.level < 30 ? [requiredValidatorV2(props.item.parent_id, '상위 영업점')] : []"
                                                />
                                        </VCol>
                                    </VRow>
                                </VCol>
                            </VRow>
                        </VCol>

                        <VCol cols="12" v-if="getUserLevel() >= 35">
                            <VRow>
                                <VCol cols="12" md="6" v-if="corp.id !== 30">
                                    <VRow no-gutters style="align-items: center;">
                                        <VCol>화면 타입</VCol>
                                        <VCol md="8">
                                    <BooleanRadio :radio="props.item.view_type"
                                        @update:radio="props.item.view_type = $event">
                                        <template #true>상세보기</template>
                                        <template #false>간편보기</template>
                                    </BooleanRadio>
                                        </VCol>
                                    </VRow>
                                </VCol>
                                <VCol cols="12" md="6">
                                    <VRow no-gutters style="align-items: center;">
                                        <VCol>하위 가맹점 수정권한</VCol>
                                        <VCol md="6">                                            
                                            <BooleanRadio :radio="props.item.is_able_modify_mcht"
                                                @update:radio="props.item.is_able_modify_mcht = $event">
                                                <template #true>가능</template>
                                                <template #false>불가능</template>
                                            </BooleanRadio>
                                        </VCol>
                                    </VRow>
                                </VCol>
                            </VRow>
                        </VCol>

                        <VCol v-if="isAbleModiy(props.item.id)">
                            <VTextarea v-model="props.item.note" counter label="메모사항"
                                prepend-inner-icon="twemoji-spiral-notepad" maxlength="300" auto-grow />
                        </VCol>                        
                    </VRow>
                </VCardItem>
            </VCard>
            <br>
            <VCard v-if="getUserLevel() >= 35">
                <VCardItem>
                    <VCol cols="12">
                        <VRow>
                            <UnderAutoSettingCard :item="props.item" />
                        </VRow>
                    </VCol>
                </VCardItem>
            </VCard>
        </VCol>
        <VCol cols="12" md="6" v-if="getUserLevel() >= 35 && props.item.id">
            <MchtBatchOverview ref="mchtBatchOverview" :selected_idxs="[]" :selected_sales_id="props.item.id" :selected_level="props.item.level"/>
            <br>
            <PayModuleBatchOverview ref="payModuleBatchOverview" :selected_idxs="[]" :selected_sales_id="props.item.id" :selected_level="props.item.level"/>
        </VCol>
    </VRow>
</template>
