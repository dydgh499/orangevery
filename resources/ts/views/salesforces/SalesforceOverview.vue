<script lang="ts" setup>
import BatchDialog from '@/layouts/dialogs/BatchDialog.vue'
import SalesRecommenderCodeDialog from '@/layouts/dialogs/salesforces/SalesRecommenderCodeDialog.vue'
import BaseQuestionTooltip from '@/layouts/tooltips/BaseQuestionTooltip.vue'
import BooleanRadio from '@/layouts/utils/BooleanRadio.vue'
import UnderAutoSettingCard from '@/views/salesforces/under-auto-settings/UnderAutoSettingCard.vue'

import { settleCycles, settleDays, settleTaxTypes, authLevels, useSalesFilterStore } from '@/views/salesforces/useStore'
import type { Options, Salesforce } from '@/views/types'

import { getLevelByIndex, getUserLevel, isAbleModiyV2, salesLevels, user_info } from '@axios'
import { ItemTypes } from '@core/enums'
import corp from '@corp'
import { requiredValidatorV2 } from '@validators'
import { useStore } from '../services/pay-gateways/useStore'

interface Props {
    item: Salesforce,
}
const props = defineProps<Props>()

const all_cycles = settleCycles()
const all_days = settleDays()
const tax_types = settleTaxTypes()
const { sales } = useSalesFilterStore()
const { pgs, pss, psFilter, setFee } = useStore()

const mchtBatchDialog = ref()
const pmodBatchDialog = ref()
const salesRecommenderCodeDialog = ref()

const getSalesLevel = () => {
    if(props.item.id)
        return salesLevels()
    else {
        const levels = corp.pv_options.auth.levels
        const sales = <Options[]>([])
        if(levels.sales0_use && getUserLevel() > 13)
            sales.push({id: 13, title: levels.sales0_name})
        if(levels.sales1_use && getUserLevel() > 15)
            sales.push({id: 15, title: levels.sales1_name})
        if(levels.sales2_use && getUserLevel() > 17)
            sales.push({id: 17, title: levels.sales2_name})
        if(levels.sales3_use && getUserLevel() > 20)
            sales.push({id: 20, title: levels.sales3_name})
        if(levels.sales4_use && getUserLevel() > 25)
            sales.push({id: 25, title: levels.sales4_name})
        if(levels.sales5_use && getUserLevel() > 30)
            sales.push({id: 30, title: levels.sales5_name})
        return sales
    }
}

const initParentId = computed(() => {
    // level 안쓰면 추적안됨
    if(props.item.id === 0 && props.item.level)
        props.item.parent_id = null
})

const getParentSales = computed(()  => {
    const idx = getLevelByIndex(props.item.level)
    if(idx < 5) {
        return sales[idx+1].value
    }
    else
        return []
})

const filterPgs = computed(() => {
    const filter = pss.filter(item => { return item.pg_id == props.item.mcht_pg_id })
    props.item.mcht_ps_id = psFilter(filter, props.item.mcht_ps_id)
    return filter
})

const getAuthLevels = computed(() => {
    if(getUserLevel() >= 35)
        return authLevels()
    else if(getUserLevel() >= 13) {
        if(isAbleModiyV2(props.item, 'salesforces')) {
            const auth_levels = []
            if(user_info.value.auth_level >= 0) 
                auth_levels.push({id:0, title:'권한없음'})
            if(user_info.value.auth_level >= 1) 
                auth_levels.push({id:1, title:'추가가능'})
            if(user_info.value.auth_level >= 2) 
                auth_levels.push({id:2, title:'추가/수정/삭제 가능'})
            return auth_levels
        }
        else
            return authLevels()
    }
    else
        return []
})

if(props.item.id === 0 && getSalesLevel().length > 0)
    props.item.level = getSalesLevel()[0].id as number

</script>
<template>
    <VRow>
        <VCol cols="12" md="6">
            <VCard>
                <VCardItem>
                    <VCardTitle>
                        <div style="display: flex;align-items: center;justify-content: space-between;">
                            <VCardTitle style="margin-right: 1em;">영업라인 정보</VCardTitle>
                            <div v-if="props.item.id"
                                :style="$vuetify.display.smAndDown ? 'display: inline-flex;flex-direction: column;' : 'display: inline-flex;'">
                                <template v-if="getUserLevel() >= 35">
                                    <VBtn style='margin: 0.25em;' variant="tonal" size="small" @click="mchtBatchDialog.show()">
                                        하위 가맹점 일괄작업
                                    </VBtn>
                                    <VBtn style='margin: 0.25em;' variant="tonal" size="small" color="error" @click="pmodBatchDialog.show()">
                                        하위 결제모듈 일괄작업
                                    </VBtn>
                                </template>
                                <VBtn v-if="corp.pv_options.paid.brand_mode === 1 && props.item.level === 13"
                                    style='margin: 0.25em;' variant="tonal" size="small" color="warning" @click="salesRecommenderCodeDialog.show(props.item)">
                                    추천인코드 관리
                                </VBtn>
                            </div>
                        </div>
                    </VCardTitle>
                    <VRow class="pt-5">
                        <VCol cols="12">
                            <VRow>
                                <VCol cols="12" md="6">
                                    <VRow no-gutters style="align-items: center;" v-if="isAbleModiyV2(props.item, 'salesforces')">
                                        <VCol cols="4">* 영업라인 상호</VCol>
                                        <VCol md="8">
                                            <VTextField v-model="props.item.sales_name" prepend-inner-icon="tabler-building-store"
                                                placeholder="상호를 입력해주세요" persistent-placeholder :rules="[requiredValidatorV2(props.item.sales_name, '영업라인 상호')]" />
                                        </VCol>
                                    </VRow>
                                    <VRow v-else>
                                        <VCol class="font-weight-bold">영업라인 상호</VCol>
                                        <VCol md="8"><span>{{ props.item.sales_name }}</span></VCol>
                                    </VRow>
                                </VCol>

                                <VCol cols="12" md="6">
                                    <VRow no-gutters style="align-items: center;" v-if="isAbleModiyV2(props.item, 'salesforces')">
                                        <VCol cols="4">
                                            <BaseQuestionTooltip :location="'top'" :text="'등급'" :content="'영업자 등급은 추가 후 수정할 수 없습니다.'"/>
                                        </VCol>
                                        <VCol md="8">                                             
                                            <VSelect :menu-props="{ maxHeight: 400 }" v-model="props.item.level"
                                                @change="initParentId" 
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
                        <VCol cols="12" v-if="isAbleModiyV2(props.item, 'salesforces')">
                            <VRow>
                                <VCol cols="12" md="6">
                                    <VRow no-gutters style="align-items: center;">
                                        <VCol>사업자명</VCol>
                                        <VCol md="8">
                                            <VTextField v-model="props.item.sales_sub_name" prepend-inner-icon="tabler-building-store"
                                            placeholder="사업자명을 입력해주세요" persistent-placeholder />
                                        </VCol>
                                    </VRow>
                                </VCol>
                                <VCol cols="12" md="6">
                                    <VRow no-gutters style="align-items: center;">
                                        <VCol>이메일</VCol>
                                        <VCol md="8">
                                            <VTextField v-model="props.item.email" prepend-inner-icon="material-symbols:mail"
                                                placeholder="이메일을 입력해주세요" persistent-placeholder>
                                            </VTextField>
                                        </VCol>
                                    </VRow>
                                </VCol>
                            </VRow>
                        </VCol>
                        <VCol cols="12" v-else>
                            <VRow>
                                <VCol cols="12" md="6">
                                    <VRow no-gutters style="align-items: center;">
                                        <VCol class="font-weight-bold">사업자명</VCol>
                                        <VCol md="8">
                                            <span>{{ props.item.sales_sub_name }} </span>
                                        </VCol>
                                    </VRow>
                                </VCol>
                                <VCol cols="12" md="6">
                                    <VRow no-gutters style="align-items: center;">
                                        <VCol class="font-weight-bold">이메일</VCol>
                                        <VCol md="8"><span>{{ props.item.email }}</span></VCol>
                                    </VRow>
                                </VCol>
                            </VRow>
                        </VCol>
                        <template v-if="corp.pv_options.paid.sales_parent_structure">
                            <VCol cols="12" v-if="isAbleModiyV2(props.item, 'salesforces')">
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
                                            <VCol>상위 영업라인</VCol>
                                            <VCol md="8">
                                                <VAutocomplete :menu-props="{ maxHeight: 400 }" v-model="props.item.parent_id"
                                                    :items="getParentSales"
                                                    :label="'상위 영업라인 선택'"
                                                    item-title="sales_name" item-value="id" persistent-hint single-line prepend-inner-icon="ph:share-network" 
                                                    :rules="props.item.level < 30 ? [requiredValidatorV2(props.item.parent_id, '상위 영업라인')] : []"
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
                                            <VCol class="font-weight-bold">기본 수수료</VCol>
                                            <VCol md="8">
                                                <VChip :color="'default'">
                                                    {{ props.item.sales_fee }} %
                                                </VChip>
                                            </VCol>
                                        </VRow>
                                    </VCol>
                                    <VCol cols="12" md="6">
                                        <VRow no-gutters style="align-items: center;">
                                            <VCol class="font-weight-bold">상위 영업라인</VCol>
                                            <VCol md="8">                                                
                                                <span>
                                                    {{ getParentSales.find(obj => obj.id === props.item.parent_id)?.sales_name || '미존재' }}
                                                </span>
                                            </VCol>
                                        </VRow>
                                    </VCol>
                                </VRow>
                            </VCol>
                        </template>
                        <VCol cols="12">
                            <VCardTitle style="margin: 1em 0;">정산 정보</VCardTitle>
                            <VRow>
                                <VCol cols="12" md="6">
                                    <VRow no-gutters style="align-items: center;" v-if="isAbleModiyV2(props.item, 'salesforces')">
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
                                    <VRow no-gutters style="align-items: center;" v-if="isAbleModiyV2(props.item, 'salesforces')">
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
                                    <VRow no-gutters style="align-items: center;" v-if="isAbleModiyV2(props.item, 'salesforces')">
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
                            </VRow>
                        </VCol>
                        <VCol cols="12">
                            <VCardTitle style="margin: 1em 0;">권한 정보</VCardTitle>
                            <VRow>
                                <VCol cols="12" md="6" v-if="isAbleModiyV2(props.item, 'salesforces')">
                                    <VRow no-gutters style="align-items: center;">
                                        <VCol md="4">
                                            <BaseQuestionTooltip :location="'top'" :text="'작업권한'" 
                                                :content="'하위 가맹점과 하위 등급의 영업라인들의 작업권한을 부여합니다.<br>(개인정보는 수정할 수 없습니다.)'"/>
                                        </VCol>
                                        <VCol md="8">                                            
                                            <VSelect :menu-props="{ maxHeight: 400 }" v-model="props.item.auth_level" 
                                                    :items="getAuthLevels" item-title="title" item-value="id" label="영업라인 권한"/>
                                        </VCol>
                                    </VRow>
                                </VCol>
                                <VCol cols="12" md="6">
                                    <VRow no-gutters style="align-items: center;" v-if="isAbleModiyV2(props.item, 'salesforces')">
                                        <VCol cols="4">화면 타입</VCol>
                                        <VCol md="8">
                                            <BooleanRadio :radio="props.item.view_type"
                                                @update:radio="props.item.view_type = $event">
                                                <template #true>상세보기</template>
                                                <template #false>간편보기</template>
                                            </BooleanRadio>
                                        </VCol>
                                    </VRow>
                                    <VRow v-else>
                                        <VCol cols="4" class="font-weight-bold">화면 타입</VCol>
                                        <VCol md="8">
                                            <VChip :color="props.item.view_type ? 'success' : 'default'">
                                                {{ props.item.view_type ? '상세보기' : '간편보기' }}
                                            </VChip>
                                        </VCol>
                                    </VRow>
                                </VCol>
                            </VRow>
                            <VRow  v-if="getUserLevel() >= 35">
                                <VCol cols="12" md="6">
                                    <VRow no-gutters style="align-items: center;">
                                        <VCol>
                                            <BaseQuestionTooltip :location="'top'" :text="'하위 가맹점 언락권한'" 
                                                :content="'하위 가맹점의 계정잠금해제, 패스워드변경 권한을 부여합니다.<br>하위 모든 가맹점의 패스워드, LOCK 상태를 제어할 수 있으므로 설정 시 해당 영업라인은 2FA 설정을 권장합니다.'"/>
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
                            </VRow>
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
                            <UnderAutoSettingCard :item="props.item" :key="props.item.id" />
                        </VRow>
                    </VCol>
                </VCardItem>
            </VCard>
        </VCol>
        <VCol v-if="corp.pv_options.paid.brand_mode === 2">
            <VCard v-if="props.item.level === 30">
                <VCardItem>
                    <VCardTitle>
                        <div style="display: flex;align-items: center;justify-content: space-between;">
                            <span style="margin-right: 1em;">옵션정보</span>
                        </div>
                    </VCardTitle>
                    <br>
                    <VCardSubtitle style="display: flex; align-items: center; justify-content: space-between;">
                        <VChip variant="outlined">하위 가맹점 출금한도</VChip>
                    </VCardSubtitle>
                    <VRow>
                        <VCol cols="12">
                            <VRow>
                                <VCol cols="12" md="6">
                                    <VRow no-gutters style="align-items: center;" v-if="isAbleModiyV2(props.item, 'salesforces')">
                                        <VCol md="5" cols="6">
                                            <span>일 출금한도(영업일)</span>
                                        </VCol>
                                        <VCol md="7">
                                            <VTextField prepend-inner-icon="tabler-currency-won"
                                                    v-model="props.item.withdraw_business_limit" type="number" suffix="만원"/>
                                        </VCol>
                                    </VRow>
                                    <VRow v-else>
                                        <VCol md="5" cols="6">
                                            <span class="font-weight-bold">일 출금한도(영업일)</span>
                                        </VCol>
                                        <VCol md="7" cols="6">
                                            {{ props.item.withdraw_business_limit }} 만원
                                        </VCol>
                                    </VRow>
                                </VCol>
                                <VCol cols="12" md="6">
                                    <VRow no-gutters style="align-items: center;" v-if="isAbleModiyV2(props.item, 'salesforces')">
                                        <VCol cols="5">일 출금한도(휴무일)</VCol>
                                        <VCol md="7"> 
                                            <VTextField prepend-inner-icon="tabler-currency-won"
                                                v-model="props.item.withdraw_holiday_limit" type="number" suffix="만원"/>
                                        </VCol>
                                    </VRow>
                                    <VRow v-else>
                                        <VCol md="5" cols="6" class="font-weight-bold">일 출금한도(휴무일)</VCol>
                                        <VCol md="7" cols="6"><span>{{ props.item.withdraw_holiday_limit }} 만원</span></VCol>
                                    </VRow>
                                </VCol>
                            </VRow>
                        </VCol>
                    </VRow>
                    <template v-if="getUserLevel() >= 35">
                        <br>
                        <VCardSubtitle style="display: flex; align-items: center; justify-content: space-between;">
                            <VChip variant="outlined">하위 가맹점 추가정보</VChip>    
                        </VCardSubtitle>
                        <VRow>
                            <VCol cols="12">
                                <VRow>
                                    <VCol cols="12" md="6">
                                        <VRow no-gutters style="align-items: center;">
                                            <VCol md="5" cols="6">
                                                <span>원천사</span>
                                            </VCol>
                                            <VCol md="7">
                                                <VSelect :menu-props="{ maxHeight: 400 }" v-model="props.item.mcht_pg_id" :items="pgs"
                                                    prepend-inner-icon="ph-buildings" label="원천사 선택" item-title="pg_name" item-value="id"
                                                    :rules="[requiredValidatorV2(props.item.mcht_pg_id, 'PG사')]" />
                                            </VCol>
                                        </VRow>
                                    </VCol>
                                    <VCol cols="12" md="6">
                                        <VRow no-gutters style="align-items: center;">
                                            <VCol cols="5">구간</VCol>
                                            <VCol md="7"> 
                                                <VSelect :menu-props="{ maxHeight: 400 }" v-model="props.item.mcht_ps_id" :items="filterPgs"
                                                    prepend-inner-icon="mdi-vector-intersection" label="구간 선택" item-title="name" item-value="id"
                                                    :hint="`${setFee(pss, props.item.mcht_ps_id)}`" persistent-hint
                                                    :rules="[requiredValidatorV2(props.item.mcht_ps_id, '구간')]" />
                                            </VCol>
                                        </VRow>
                                    </VCol>
                                </VRow>
                            </VCol>
                        </VRow>
                    </template>
                    <br>
                    <VCardSubtitle style="display: flex; align-items: center; justify-content: space-between;">
                        <VChip variant="outlined">재판매 옵션</VChip>    
                    </VCardSubtitle>
                    <VRow>
                        <VCol cols="12">
                            <VRow>
                                <VCol cols="12" md="6">
                                    <VRow no-gutters style="align-items: center;" v-if="isAbleModiyV2(props.item, 'salesforces')">
                                        <VCol md="5" cols="6">
                                            <span>지급대행 수수료</span>
                                        </VCol>
                                        <VCol md="7">
                                            <VTextField prepend-inner-icon="tabler-currency-won"
                                                    v-model="props.item.resale_withdraw_fee" type="number" suffix="원"/>
                                        </VCol>
                                    </VRow>
                                    <VRow v-else>
                                        <VCol md="5" cols="6">
                                            <span class="font-weight-bold">지급대행 수수료</span>
                                        </VCol>
                                        <VCol md="7" cols="6">
                                            {{ props.item.withdraw_business_limit }} 원
                                        </VCol>
                                    </VRow>
                                </VCol>
                                <VCol cols="12" md="6">
                                    <VRow no-gutters style="align-items: center;" v-if="isAbleModiyV2(props.item, 'salesforces')">
                                        <VCol cols="5">건별 수수료</VCol>
                                        <VCol md="7"> 
                                            <VTextField prepend-inner-icon="tabler-currency-won"
                                                v-model="props.item.resale_settle_fee" type="number" suffix="원"/>
                                        </VCol>
                                    </VRow>
                                    <VRow v-else>
                                        <VCol md="5" cols="6" class="font-weight-bold">건별 수수료</VCol>
                                        <VCol md="7" cols="6"><span>{{ props.item.withdraw_holiday_limit }} 원</span></VCol>
                                    </VRow>
                                </VCol>
                            </VRow>
                        </VCol>
                    </VRow>
                </VCardItem>
            </VCard>
        </VCol>
        <BatchDialog ref="mchtBatchDialog" :selected_idxs="[]" :selected_sales_id="props.item.id" :selected_level="props.item.level"
            :item_type="ItemTypes.Merchandise" @update:select_idxs=""/>
        <BatchDialog ref="pmodBatchDialog" :selected_idxs="[]" :selected_sales_id="props.item.id" :selected_level="props.item.level"
            :item_type="ItemTypes.PaymentModule" @update:select_idxs=""/>
        <SalesRecommenderCodeDialog ref="salesRecommenderCodeDialog" :key="props.item.id"/>
    </VRow>
</template>
<style scoped>
:deep(.v-row) {
  align-items: center;
}
</style>
