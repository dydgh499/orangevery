<script lang="ts" setup>
import BatchDialog from '@/layouts/dialogs/BatchDialog.vue'
import BaseQuestionTooltip from '@/layouts/tooltips/BaseQuestionTooltip.vue'
import BooleanRadio from '@/layouts/utils/BooleanRadio.vue'
import { isDistMchtFeeModifyAble } from '@/plugins/fixplus'
import UnderAutoSettingCard from '@/views/salesforces/under-auto-settings/UnderAutoSettingCard.vue'
import { settleCycles, settleDays, settleTaxTypes, useSalesFilterStore } from '@/views/salesforces/useStore'
import type { Options, Salesforce } from '@/views/types'

import { getLevelByIndex, getUserLevel, isAbleModiy, salesLevels } from '@axios'
import { ItemTypes } from '@core/enums'
import corp from '@corp'
import { requiredValidatorV2 } from '@validators'

interface Props {
    item: Salesforce,
}
const props = defineProps<Props>()

const all_cycles = settleCycles()
const all_days = settleDays()
const tax_types = settleTaxTypes()
const { sales } = useSalesFilterStore()

const mchtBatchDialog = ref()
const pmodBatchDialog = ref()


const getSalesLevel = () => {
    if(props.item.id)
        return salesLevels()
    else {
        const levels = corp.pv_options.auth.levels
        const sales = <Options[]>([])
        if(levels.sales0_use && getUserLevel() >= 13)
            sales.push({id: 13, title: levels.sales0_name})
        if(levels.sales1_use && getUserLevel() >= 15)
            sales.push({id: 15, title: levels.sales1_name})
        if(levels.sales2_use && getUserLevel() >= 17)
            sales.push({id: 17, title: levels.sales2_name})
        if(levels.sales3_use && getUserLevel() >= 20)
            sales.push({id: 20, title: levels.sales3_name})
        if(levels.sales4_use && getUserLevel() >= 25)
            sales.push({id: 25, title: levels.sales4_name})
        if(levels.sales5_use && getUserLevel() >= 30)
            sales.push({id: 30, title: levels.sales5_name})
        return sales
    }
}

const getParentSales = computed(()  => {
    const idx = getLevelByIndex(props.item.level)
    if(idx < 5) {
        return sales[idx+1].value
    }
    else
        return []
})

if(props.item.id === 0 && getSalesLevel().length > 0)
    props.item.level = getSalesLevel()[0].id as number

</script>
<template>
    <VRow>
        <!-- 👉 개인정보 -->
        <VCol cols="12" md="6">
            <VCard>
                <VCardItem>
                    <VCardTitle>
                        <div style="display: flex;align-items: center;justify-content: space-between;">
                            <span style="margin-right: 1em;">영업점정보</span>
                            <div v-if="getUserLevel() >= 35 && props.item.id"
                                :style="$vuetify.display.smAndDown ? 'display: inline-flex;flex-direction: column;' : 'display: inline-flex;'">
                                <VBtn style='margin: 0.25em;' variant="tonal" size="small" @click="mchtBatchDialog.show()">
                                    하위 가맹점 일괄작업
                                </VBtn>
                                <VBtn style='margin: 0.25em;' variant="tonal" size="small" color="error" @click="pmodBatchDialog.show()">
                                    하위 결제모듈 일괄작업
                                </VBtn>
                            </div>
                        </div>
                    </VCardTitle>
                    <VRow class="pt-5">
                        <VCol cols="12">
                            <VRow>
                                <VCol cols="12" md="6">
                                    <VRow no-gutters style="align-items: center;" v-if="isAbleModiy(props.item.id)">
                                        <VCol cols="4">* 영업점 상호</VCol>
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

                                <VCol cols="12" md="6">
                                    <VRow no-gutters style="align-items: center;" v-if="isAbleModiy(props.item.id)">
                                        <VCol cols="4">
                                            <BaseQuestionTooltip :location="'top'" :text="'등급'" :content="'영업자 등급은 추가 후 수정할 수 없습니다.'"/>
                                        </VCol>
                                        <VCol md="8">                                             
                                            <VSelect :menu-props="{ maxHeight: 400 }" v-model="props.item.level"
                                                :items="getSalesLevel()" prepend-inner-icon="ph:share-network" label="영업자 등급 선택"
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
                        <template v-if="corp.pv_options.paid.sales_parent_structure">
                            <VCol cols="12" v-if="isAbleModiy(props.item.id)">
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
                            <VCol cols="12" v-else>
                                <VRow>
                                    <VCol cols="12" md="6">
                                        <VRow no-gutters style="align-items: center;">
                                            <VCol>기본 수수료</VCol>
                                            <VCol md="8">
                                                <VChip :color="'default'">
                                                    {{ props.item.sales_fee }} %
                                                </VChip>
                                            </VCol>
                                        </VRow>
                                    </VCol>
                                    <VCol cols="12" md="6">
                                        <VRow no-gutters style="align-items: center;">
                                            <VCol>상위 영업점</VCol>
                                            <VCol md="8">                                                
                                                <span>
                                                    {{ getParentSales.find(obj => obj.id === props.item.parent_id)?.sales_name }}
                                                </span>
                                            </VCol>
                                        </VRow>
                                    </VCol>
                                </VRow>
                            </VCol>
                        </template>
                        <VCol cols="12">
                            <VRow>
                                <VCol cols="12" md="6">
                                    <VRow no-gutters style="align-items: center;" v-if="isAbleModiy(props.item.id)">
                                        <VCol cols="4">정산 주기</VCol>
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
                                        <VCol cols="4">정산 요일</VCol>
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
                                <VCol cols="12" md="6">
                                    <VRow no-gutters style="align-items: center;" v-if="isAbleModiy(props.item.id)">
                                        <VCol cols="4">정산 세율</VCol>
                                        <VCol md="8">
                                            <VSelect :menu-props="{ maxHeight: 400 }" v-model="props.item.settle_tax_type" :items="tax_types"
                                                prepend-inner-icon="tabler:tax" label="정산 세율 선택" item-title="title" 
                                                item-value="id" persistent-hint single-line :rules="[requiredValidatorV2(props.item.settle_tax_type, '정산 세율')]"/>
                                        </VCol>
                                    </VRow>
                                    <VRow v-else>
                                        <VCol class="font-weight-bold">정산 세율</VCol>
                                        <VCol md="8"><span>{{ tax_types.find(obj => obj.id === props.item.settle_tax_type).title }}</span></VCol>
                                    </VRow>
                                </VCol>
                                
                                <VCol cols="12" md="6" v-if="getUserLevel() >= 35">
                                    <VRow no-gutters style="align-items: center;">
                                        <VCol cols="4">화면 타입</VCol>
                                        <VCol md="8">
                                    <BooleanRadio :radio="props.item.view_type"
                                        @update:radio="props.item.view_type = $event">
                                        <template #true>상세보기</template>
                                        <template #false>간편보기</template>
                                    </BooleanRadio>
                                        </VCol>
                                    </VRow>
                                </VCol>
                            </VRow>
                        </VCol>
                        <VCol cols="12" v-if="getUserLevel() >= 35">
                            <VRow>
                                <VCol cols="12" md="6">
                                    <VRow no-gutters style="align-items: center;">
                                        <VCol>
                                            <BaseQuestionTooltip :location="'top'" :text="'하위 가맹점 언락권한'" 
                                                :content="'하위 가맹점의 계정잠금해제, 패스워드변경 권한을 부여합니다.<br>하위 모든 가맹점의 패스워드, LOCK 상태를 제어할 수 있으므로 설정 시 해당 영업점은 2FA 설정을 권장합니다.'"/>
                                        </VCol>
                                        <VCol md="6">                                            
                                            <BooleanRadio :radio="props.item.is_able_unlock_mcht"
                                                @update:radio="props.item.is_able_unlock_mcht = $event">
                                                <template #true>가능</template>
                                                <template #false>불가능</template>
                                            </BooleanRadio>
                                        </VCol>
                                    </VRow>
                                </VCol>
                                <VCol cols="12" md="6">
                                    <VRow no-gutters style="align-items: center;">
                                        <VCol md="4">
                                            {{
                                                corp.pv_options.paid.sales_parent_structure
                                                ?
                                                "생성권한"
                                                :
                                                "생성/수정권한"    
                                            }}
                                        </VCol>
                                        <VCol md="8">
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
        </VCol>
        <VCol cols="12" md="6" v-if="getUserLevel() >= 35 && corp.pv_options.paid.sales_parent_structure === false">
            <VCard>
                <VCardItem>
                    <VCol cols="12">
                        <VRow>
                            <UnderAutoSettingCard :item="props.item" />
                        </VRow>
                    </VCol>
                </VCardItem>
            </VCard>
        </VCol>
        <BatchDialog ref="mchtBatchDialog" :selected_idxs="[]" :selected_sales_id="props.item.id" :selected_level="props.item.level"
            :item_type="ItemTypes.Merchandise" @update:select_idxs=""/>
        <BatchDialog ref="pmodBatchDialog" :selected_idxs="[]" :selected_sales_id="props.item.id" :selected_level="props.item.level"
            :item_type="ItemTypes.PaymentModule" @update:select_idxs=""/>
    </VRow>
</template>
<style scoped>
:deep(.v-row) {
  align-items: center;
}
</style>
