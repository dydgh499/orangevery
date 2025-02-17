<script lang="ts" setup>
import BatchDialog from '@/layouts/dialogs/BatchDialog.vue'
import BaseQuestionTooltip from '@/layouts/tooltips/BaseQuestionTooltip.vue'
import BooleanRadio from '@/layouts/utils/BooleanRadio.vue'
import type { Options, Salesforce } from '@/views/types'
import { banks, getOnlyNumber } from '@/views/users/useStore'
import { ItemTypes } from '@core/enums'

import { autoUpdateSalesforceInfo, isDistMchtFeeModifyAble } from '@/plugins/fixplus'
import { useSalesFilterStore } from '@/views/salesforces/useStore'
import { axios, getLevelByIndex, getUserLevel, isAbleModiy, salesLevels } from '@axios'
import corp from '@corp'
import { requiredValidatorV2 } from '@validators'

interface Props {
    item: Salesforce,
}
const props = defineProps<Props>()

const alert = <any>(inject('alert'))
const snackbar = <any>(inject('snackbar'))
const errorHandler = <any>(inject('$errorHandler'))
const formatDate = <any>(inject('$formatDate'))

const { sales, all_sales } = useSalesFilterStore()

const is_show = ref(false)
const is_resident_num_back_show = ref(false)

const mchtBatchDialog = ref()
const pmodBatchDialog = ref()

const setMchtFee = async () => {
    const getCommonParams = () => {
        return {
            'selected_idxs': [],
            'selected_sales_id': props.item.id,
            'selected_level': props.item.level,
        }
    }
    const post = async (page: string, params: any) => {
        try {
            if (await alert.value.show('정말 해당 지사의 하위 가맹점 수수료율을 <b>'+props.item.mcht_batch_fee+'%</b>로 일괄적용하시겠습니까?')) {
                Object.assign(params, getCommonParams())
                const r = await axios.post('/api/v1/manager/merchandises/batch-updaters/' + page, params)
                await axios.post('/api/v1/manager/salesforces/'+props.item.id+'/mcht-batch-fee', {mcht_batch_fee: props.item.mcht_batch_fee});
                snackbar.value.show('성공하였습니다.', 'success')
            }
        }
        catch (e: any) {
            snackbar.value.show(e.response.data.message, 'error')
            const r = errorHandler(e)
        }
    }
    const res = await post('mcht-fee-direct-apply', {
        'mcht_fee': props.item.mcht_batch_fee,
        'hold_fee': 0,
        'apply_dt': formatDate(new Date),
    })
}

const setAcctBankName = () => {
    const bank = banks.find(obj => obj.code == props.item.acct_bank_code)
    props.item.acct_bank_name = bank ? bank.title : '선택안함'
}

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

const setDefaultLevel = () => {
    const sales_levels = getSalesLevel()
    if(props.item.id === 0 && sales_levels.length > 0) {
        let length = sales_levels.length
        if(length - 2 >= 0)
            props.item.level = sales_levels[length - 2].id as number
        else
            props.item.level = sales_levels[length - 1].id as number
    }
}
const initParentSales = () => {
    if(props.item.id === 0 && corp.pv_options.paid.sales_parent_structure)
        props.item.parent_id = null
}

const getParentSales = computed(()  => {
    const idx = getLevelByIndex(props.item.level)
    if(idx < 5) {
        return sales[idx+1].value
    }
    else
        return []
})

const TrxFeeReadonly = () => {
    if(getUserLevel() >= 30)
        return true
    else
    {
        if(props.item.id !== 0) {
            if(getUserLevel() <= props.item.level)
                return false
            else
                return true
        }
        else {
            return isDistMchtFeeModifyAble(all_sales)
        }
    }
}

setDefaultLevel()
watchEffect(() => {
    if(props.item.id === 0 )
        autoUpdateSalesforceInfo(props.item)
})
</script>
<template>
    <VRow>
        <VCol cols="12" md="6">
            <VCard>
                <VCardItem>
                    <VCardTitle>
                        <div style="display: flex;align-items: center;justify-content: space-between;">
                            <span style="margin-right: 1em;">기본정보</span>
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
                    <VRow class="pt-3">
                        <VCol cols="12" md="6">
                            <VRow no-gutters v-if="isAbleModiy(props.item.id)">
                                <VCol>
                                    <label>* 아이디</label>
                                </VCol>
                                <VCol md="8"> 
                                    <VTextField type='text' v-model="props.item.user_name" prepend-inner-icon="tabler-mail"
                                        placeholder="아이디 입력" persistent-placeholder :rules="[requiredValidatorV2(props.item.user_name, '아이디')]"
                                        maxlength="30" @update:model-value="props.item.user_name= $event.trim()"/>
                                </VCol>
                            </VRow>
                            <VRow v-else>
                                <VCol class="font-weight-bold">아이디</VCol>
                                <VCol md="8"><span>{{ props.item.user_name }}</span></VCol>
                            </VRow>
                        </VCol>
                        <VCol cols="12" md="6" v-if="props.item.id == 0">
                            <VRow no-gutters>
                                <VCol>
                                    <label>* 패스워드</label>
                                </VCol>
                                <VCol md="8">
                                    <VTextField v-model="props.item.user_pw" counter prepend-inner-icon="tabler-lock"
                                    :rules="[requiredValidatorV2(props.item.user_pw, '패스워드')]"
                                    :append-inner-icon="is_show ? 'tabler-eye' : 'tabler-eye-off'"
                                    :type="is_show ? 'text' : 'password'" persistent-placeholder
                                    @click:append-inner="is_show = !is_show" autocomplete="new-password" />
                                </VCol>
                            </VRow>
                        </VCol>
                    </VRow>
                    <VRow>
                        <VCol cols="12" md="6">
                            <VRow no-gutters v-if="isAbleModiy(props.item.id)">
                                <VCol>
                                    <label>* 대표자명</label>
                                </VCol>
                                <VCol md="8">
                                    <VTextField id="nickNameHorizontalIcons" v-model="props.item.nick_name"
                                    prepend-inner-icon="tabler-user" placeholder="대표자명 입력" persistent-placeholder
                                    v-if="isAbleModiy(props.item.id)"
                                    :rules="[requiredValidatorV2(props.item.nick_name, '대표자명')]"/>
                                    <span v-else>{{ props.item.nick_name }}</span>
                                </VCol>
                            </VRow>
                            <VRow v-else>
                                <VCol class="font-weight-bold">대표자명</VCol>
                                <VCol md="8"><span>{{ props.item.nick_name }}</span></VCol>
                            </VRow>
                        </VCol>
                        <VCol cols="12" md="6">
                            <VRow no-gutters v-if="isAbleModiy(props.item.id)">
                                <VCol>
                                    <label>* 휴대폰번호</label>
                                </VCol>
                                <VCol md="8">
                                    <VTextField v-model="props.item.phone_num" type="text"
                                    prepend-inner-icon="tabler-device-mobile" placeholder="010-0000-0000"
                                    persistent-placeholder maxlength="13" 
                                    :rules="[requiredValidatorV2(props.item.phone_num, '휴대폰번호')]"/>                                    
                                </VCol>
                            </VRow>
                            <VRow v-else>
                                <VCol class="font-weight-bold">휴대폰번호</VCol>
                                <VCol md="8"><span>{{ props.item.phone_num }}</span></VCol>
                            </VRow>
                        </VCol>
                    </VRow>
                    
                    <VRow>
                        <VCol cols="12" md="12">
                            <VRow no-gutters v-if="isAbleModiy(props.item.id)">
                                <VCol>
                                    <label>주소</label>
                                </VCol>
                                <VCol md="10">
                                    <VTextField v-model="props.item.addr"
                                    prepend-inner-icon="tabler-map-pin" placeholder="주소 입력" persistent-placeholder
                                    maxlength="200"/>
                                </VCol>
                            </VRow>
                            <VRow v-else>
                                <VCol class="font-weight-bold">주소</VCol>
                                <VCol md="10"><span>{{ props.item.addr }}</span></VCol>
                            </VRow>
                        </VCol>
                    </VRow>
                    
                    <VRow>
                        <VCol cols="12">
                            <VRow no-gutters v-if="isAbleModiy(props.item.id)">
                                <VCol>
                                    <label>사업자등록번호</label>
                                </VCol>
                                <VCol md="10">
                                    <div style="display: flex;">
                                        <VTextField v-model="props.item.business_num" type="text"
                                            prepend-inner-icon="ic-outline-business-center" placeholder="1231212345"
                                            persistent-placeholder maxlength="13">
                                            <VTooltip activator="parent" location="top" v-if="corp.use_different_settlement">
                                                {{ "사업자번호를 입력하지 않거나, 정확하게 입력하지 않으면 차액정산대상에서 제외됩니다." }}
                                            </VTooltip>
                                        </VTextField>
                                    </div>
                                </VCol>
                            </VRow>
                            <VRow v-else>
                                <VCol class="font-weight-bold">사업자등록번호</VCol>
                                <VCol md="10"><span>{{ props.item.business_num }}</span></VCol>
                            </VRow>
                        </VCol>
                    </VRow>
                    <VRow>
                        <VCol cols="12">
                            <VRow no-gutters v-if="isAbleModiy(props.item.id)">
                                <VCol md=2 cols="12">
                                    <label>주민등록번호</label>
                                </VCol>
                                <VCol md="10" cols="12">
                                    <VRow style="align-items: center;">
                                        <VCol md="8" :cols="12" style="display: flex;">
                                            <VTextField v-model="props.item.resident_num_front" type="number" id="regidentFrontNum"
                                                prepend-inner-icon="carbon-identification" placeholder="800101" maxlength="6"
                                                @update:model-value="props.item.resident_num_front = getOnlyNumber($event)"
                                                style="width: 13em;"/>
                                            <span style="margin: 0.5em;text-align: center;"> - </span>
                                            <VTextField v-model="props.item.resident_num_back" placeholder="*******" id="regidentBackNum"
                                                maxlength="7"
                                                :append-inner-icon="is_resident_num_back_show ? 'tabler-eye' : 'tabler-eye-off'"
                                                :type="is_resident_num_back_show ? 'number' : 'password'"
                                                @click:append-inner="is_resident_num_back_show = !is_resident_num_back_show" 
                                                @update:model-value="props.item.resident_num_back = getOnlyNumber($event)"
                                                style="width: 13em;"/>
                                        </VCol>
                                    </VRow>
                                </VCol>
                            </VRow>
                            <VRow v-else>
                                <VCol class="font-weight-bold">주민등록번호</VCol>
                                <VCol md="10"><span>{{ props.item.resident_num_front }} - *******</span></VCol>
                            </VRow>
                        </VCol>
                    </VRow>
                </VCardItem>
                <VCardItem v-if="isAbleModiy(props.item.id)" style="padding-top: 0;">
                    <VCardTitle>은행정보</VCardTitle>
                    <VRow class="pt-3">
                        <VCol cols="12" :md="12">
                            <VRow no-gutters>
                                <VCol md="2" cols="4">
                                    <label>계좌번호</label>
                                </VCol>
                                <VCol md="10">
                                    <VTextField id="acctNumHorizontalIcons" v-model="props.item.acct_num"
                                    prepend-inner-icon="ri-bank-card-fill" placeholder="계좌번호 입력" persistent-placeholder maxlength="20" />
                                </VCol>
                            </VRow>
                        </VCol>
                    </VRow>
                    <VRow>
                        <VCol cols="12" md="6">
                            <VRow no-gutters v-if="isAbleModiy(props.item.id)">
                                <VCol md="4" cols="5">
                                    <label>예금주</label>
                                </VCol>
                                <VCol md="8">
                                    <VTextField v-model="props.item.acct_name"
                                    prepend-inner-icon="tabler-user" placeholder="예금주 입력" persistent-placeholder maxlength="40" />
                                </VCol>
                            </VRow>
                            <VRow v-else>
                                <VCol class="font-weight-bold">예금주</VCol>
                                <VCol md="8"><span>{{ props.item.acct_name }}</span></VCol>
                            </VRow>
                        </VCol>
                        <VCol cols="12" md="6">
                            <VRow no-gutters v-if="isAbleModiy(props.item.id)">
                                <VCol md="2" cols="5">
                                    <label>은행</label>
                                </VCol>
                                <VCol md="6">
                                    <VAutocomplete :menu-props="{ maxHeight: 400 }" v-model="props.item.acct_bank_code"
                                        :items="[{ code: null, title: '선택안함' }].concat(banks)" prepend-inner-icon="ph-buildings"
                                        label="은행 선택" item-title="title" item-value="code" single-line
                                        @update:modelValue="setAcctBankName()" />
                                </VCol>
                                <VCol md="4" cols="12" :style="$vuetify.display.smAndDown ? 'text-align: end;' : ''">
                                    <h5 style="margin-top: 0.5em; margin-left: 0.5em;">
                                        {{ `은행 코드: ${props.item.acct_bank_code ? props.item.acct_bank_code : '000'} ` }}
                                    </h5>
                                </VCol>
                            </VRow>
                            <VRow v-else>
                                <VCol class="font-weight-bold">은행</VCol>
                                <VCol md="8"><span>{{ props.item.acct_bank_name }}</span></VCol>
                            </VRow>
                        </VCol>
                    </VRow>
                </VCardItem>
                <VCardItem style="padding-top: 0;">
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
                                <VCol cols="12" md="6">
                                    <VRow no-gutters style="align-items: center;" v-if="isAbleModiy(props.item.id)">
                                        <VCol>
                                            <BaseQuestionTooltip :location="'top'" :text="'등급'" :content="'영업자 등급은 수정할 수 없습니다.'"/>
                                        </VCol>
                                        <VCol md="8">                                             
                                            <VSelect :menu-props="{ maxHeight: 400 }" v-model="props.item.level"
                                                :items="getSalesLevel()" prepend-inner-icon="ph:share-network" label="영업자 등급 선택"
                                                item-title="title" item-value="id" persistent-hint single-line :rules="[requiredValidatorV2(props.item.level, '영업자 등급')]"
                                                :readonly="props.item.id != 0" @update:model-value="initParentSales()"/>
                                        </VCol>
                                    </VRow>
                                    <VRow v-else>
                                        <VCol class="font-weight-bold">등급</VCol>
                                        <VCol md="8"><span>{{ salesLevels().find(obj => obj.id === props.item.level).title }}</span></VCol>
                                    </VRow>
                                </VCol>
                            </VRow>
                        </VCol>
                        <VCol cols="12" v-if="isAbleModiy(props.item.id)">
                            <VRow>
                                <VCol cols="12" md="6">
                                    <VRow no-gutters style="align-items: center;">
                                        <VCol>기본 수수료</VCol>
                                        <VCol md="8">
                                            <VTextField v-model="props.item.sales_fee" type="number" suffix="%" :rules="[requiredValidatorV2(props.item.sales_fee, '기본 수수료')]"
                                            :readonly="!TrxFeeReadonly()"/>
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
                        <VCol cols="12" v-if="isAbleModiy(props.item.id) && getUserLevel() >= 30 && props.item.level == 25">
                            <VRow>
                                <VCol cols="12" md="6">
                                    <VRow no-gutters style="align-items: center;">
                                        <VCol>
                                            <BaseQuestionTooltip :location="'top'" :text="'하위 가맹점수수료 변경'" :content="'이 설정 값은 총판, 운영사만 확인가능한 <b>지사</b>고유 설정 값 입니다.<br><br>지사이하 하위 영업점들이 <b>하위 가맹점 수수료</b>를 수정/수정불가하게 설정할 수 있습니다.'"/>
                                        </VCol>
                                        <VCol md="6">
                                            <BooleanRadio :radio="props.item.is_able_under_modify"
                                                @update:radio="props.item.is_able_under_modify = $event">
                                                <template #true>가능</template>
                                                <template #false>불가</template>
                                            </BooleanRadio>
                                        </VCol>
                                    </VRow>
                                </VCol>
                                <VCol cols="12" md="6" v-if="props.item.id">
                                    <VRow no-gutters style="align-items: center;">
                                        <VCol>가맹점 수수료 일괄적용</VCol>
                                        <VCol md="3">
                                            <VTextField v-model="$props.item.mcht_batch_fee" type="number" suffix="%"/>
                                        </VCol>
                                        <VCol md="3">
                                            <VBtn variant="tonal" @click="setMchtFee()" size="small"
                                                style="margin-bottom: auto; margin-left: 0.5em;">
                                                즉시적용
                                            </VBtn>
                                        </VCol>
                                    </VRow>
                                </VCol>
                            </VRow>
                        </VCol>
                        <VCol cols="12" v-if="isAbleModiy(props.item.id)">
                            <VRow>
                                <VCol cols="12" md="6">
                                    <VTextarea v-model="props.item.note" counter label="메모사항"
                                        variant="filled"
                                        prepend-inner-icon="twemoji-spiral-notepad" maxlength="300" auto-grow />
                                </VCol>
                                <VCol cols="12" md="6" style="margin-bottom: auto;">
                                    <VRow no-gutters>
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
    </VRow>
</template>
<style scoped>
:deep(.v-row) {
  align-items: center;
}
</style>
