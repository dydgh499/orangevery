<script lang="ts" setup>
import { autoUpdateMerchandiseAgencyInfo, autoUpdateMerchandiseInfo, isFixplusAgency } from '@/plugins/fixplus'
import { useSalesFilterStore } from '@/views/salesforces/useStore'
import type { Merchandise } from '@/views/types'
import { banks } from '@/views/users/useStore'
import { axios, getIndexByLevel, getLevelByIndex, getUserLevel, isAbleModiy, user_info } from '@axios'
import corp from '@corp'
import { businessNumValidator, lengthValidator, requiredValidatorV2 } from '@validators'

interface Props {
    item: Merchandise,
}
const levels = corp.pv_options.auth.levels

const props = defineProps<Props>()
const alert = <any>(inject('alert'))
const snackbar = <any>(inject('snackbar'))
const errorHandler = <any>(inject('$errorHandler'))

const { sales, all_sales, initAllSales, hintSalesApplyFee, hintSalesSettleFee } = useSalesFilterStore()

const setAcctBankName = () => {
    const bank = banks.find(obj => obj.code == props.item.acct_bank_code)
    props.item.acct_bank_name = bank ? bank.title : '선택안함'
}

const onwerCheck = async () => {
    if (await alert.value.show('정말 예금주 검증을 하시겠습니까?')) {
        try {
            const params = {
                acct_cd: props.item.acct_bank_code,
                acct_num: props.item.acct_num.trim().replace('-', ''),
                acct_nm: props.item.acct_name
            }
            const r = await axios.post('/api/v1/auth/onwer-check', params)
            snackbar.value.show(r.data.message, 'success')
        }
        catch (e: any) {
            snackbar.value.show(e.response.data.message, 'error')
            const r = errorHandler(e)
        }
    }
}

initAllSales()
watchEffect(() => {
    // 수정가능, 추가상태, 영업점일 경우
    if(isAbleModiy(props.item.id) && props.item.id === 0 && getUserLevel() < 35) {
        const idx = getLevelByIndex(getUserLevel())
        props.item[`sales${idx}_id`] = user_info.value.id
    }
})
watchEffect(() => {
    if(props.item.id === 0) {
        autoUpdateMerchandiseInfo(props.item)
        if(getUserLevel() === 17 || getUserLevel() === 20) {
            autoUpdateMerchandiseAgencyInfo(props.item, all_sales)
        }
    }
})
</script>
<template>
    <VRow class="match-height">
        <VCol cols="12" md="6">
            <VCard>
                <VCardItem>
                    <VCardTitle>기본정보</VCardTitle>
                    <VRow class="pt-5">
                        <VCol cols="12" md="6">
                            <VRow no-gutters v-if="isAbleModiy(props.item.id)">
                                <VCol>
                                    <label>대표자명</label>
                                </VCol>
                                <VCol md="8">
                                    <VTextField id="nickNameHorizontalIcons" v-model="props.item.nick_name"
                                    prepend-inner-icon="tabler-user" placeholder="대표자명 입력" persistent-placeholder
                                    v-if="isAbleModiy(props.item.id)"/>
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
                                    <label>휴대폰번호</label>
                                </VCol>
                                <VCol md="8">
                                    <VTextField v-model="props.item.phone_num" type="text"
                                    prepend-inner-icon="tabler-device-mobile" placeholder="010-0000-0000"
                                    persistent-placeholder maxlength="13"
                                    :rules="[requiredValidatorV2(props.item.phone_num, '휴대폰번호'), lengthValidator(props.item.phone_num, 8)]"/>
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
                                            persistent-placeholder maxlength="13"
                                            :rules="[requiredValidatorV2(props.item.business_num, '사업자번호'), businessNumValidator(props.item.business_num)]">
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
                </VCardItem>
                
                <VCardItem v-if="isAbleModiy(props.item.id) || getUserLevel() === 10">
                    <VCardTitle>은행정보</VCardTitle>
                    <VRow class="pt-3">
                        <VCol cols="12" :md="getUserLevel() === 10 ? 6: 12">
                            <VRow no-gutters v-if="isAbleModiy(props.item.id)">
                                <VCol>
                                    <label>계좌번호</label>
                                </VCol>
                                <VCol md="10">
                                    <VTextField id="acctNumHorizontalIcons" v-model="props.item.acct_num"
                                prepend-inner-icon="ri-bank-card-fill" placeholder="계좌번호 입력" persistent-placeholder maxlength="20" 
                                :rules="[requiredValidatorV2(props.item.acct_num, '계좌번호')]"/>
                                </VCol>
                            </VRow>
                            <VRow v-else>
                                <VCol class="font-weight-bold">계좌번호</VCol>
                                <VCol md="8"><span>{{ props.item.acct_num }}</span></VCol>
                            </VRow>
                        </VCol>
                        <VCol md="6" v-if="getUserLevel() === 10">
                            <VRow>
                                <VCol class="font-weight-bold">은행코드</VCol>
                                <VCol md="8"><span>{{ props.item.acct_bank_code }}</span></VCol>
                            </VRow>
                        </VCol>
                    </VRow>
                    <VRow>
                        <VCol cols="12" md="6">
                            <VRow no-gutters v-if="isAbleModiy(props.item.id)">
                                <VCol>
                                    <label>* 예금주</label>
                                </VCol>
                                <VCol md="8">
                                    <VTextField v-model="props.item.acct_name"
                                    prepend-inner-icon="tabler-user" placeholder="예금주 입력" persistent-placeholder maxlength="40" 
                                    :rules="[requiredValidatorV2(props.item.acct_name, '예금주')]"/>
                                </VCol>
                            </VRow>
                            <VRow v-else>
                                <VCol class="font-weight-bold">예금주</VCol>
                                <VCol md="8"><span>{{ props.item.acct_name }}</span></VCol>
                            </VRow>
                        </VCol>
                        <VCol cols="12" md="6">
                            <VRow no-gutters v-if="isAbleModiy(props.item.id)">
                                <VCol>
                                    <label>은행</label>
                                </VCol>
                                <VCol md="8">
                                    <VAutocomplete :menu-props="{ maxHeight: 400 }" v-model="props.item.acct_bank_code"
                                    :items="[{ code: null, title: '선택안함' }].concat(banks)" prepend-inner-icon="ph-buildings"
                                    label="은행 선택" item-title="title" item-value="code" persistent-hint single-line
                                    :hint="`${props.item.acct_bank_name}, 은행 코드: ${props.item.acct_bank_code ? props.item.acct_bank_code : '000'} `"
                                    :rules="[requiredValidatorV2(props.item.acct_bank_code, '은행')]" @update:modelValue="setAcctBankName()" />
                                </VCol>
                            </VRow>
                            <VRow v-else>
                                <VCol class="font-weight-bold">은행</VCol>
                                <VCol md="8"><span>{{ props.item.acct_bank_name }}</span></VCol>
                            </VRow>
                        </VCol>
                    </VRow>
                    <VCol cols="12" v-if="corp.pv_options.paid.use_acct_verification && isAbleModiy(props.item.id)">
                        <VBtn @click="onwerCheck" prepend-icon="ri:pass-valid-line" class="float-right">
                            예금주 검증
                        </VBtn>
                    </VCol>
                </VCardItem>
            </VCard>
        </VCol>
        <VCol cols="12" md="6">
            <VCard>
                <VCardItem>
                    <VCardTitle>가맹점정보</VCardTitle>
                    <VRow class="pt-5">
                        <VCol cols="12">
                            <VRow>
                                <VCol cols="12" md="6">
                                    <VRow no-gutters style="align-items: center;" v-if="isAbleModiy(props.item.id)">
                                        <VCol>* 가맹점 상호</VCol>
                                        <VCol md="8">
                                            <VTextField v-model="props.item.mcht_name" prepend-inner-icon="tabler-building-store"
                                            placeholder="상호를 입력해주세요" persistent-placeholder :rules="[requiredValidatorV2(props.item.mcht_name, '가맹점 상호')]" />
                                        </VCol>
                                    </VRow>
                                    <VRow v-else>
                                        <VCol class="font-weight-bold">가맹점 상호</VCol>
                                        <VCol md="8"><span>{{ props.item.mcht_name }}</span></VCol>
                                    </VRow>
                                </VCol>
                            </VRow>
                        </VCol>
                        <VCol cols="12" v-if="corp.use_different_settlement">
                            <VRow>
                                <VCol cols="12" md="6">
                                    <VRow no-gutters style="align-items: center;" v-if="isAbleModiy(props.item.id)">
                                        <VCol>이메일</VCol>
                                        <VCol md="8"> 
                                            <VTextField v-model="props.item.email" prepend-inner-icon="material-symbols:mail"
                                                placeholder="이메일을 입력해주세요" persistent-placeholder>
                                                <VTooltip activator="parent" location="top" maxlength="50">
                                                    하위몰이 대표 이메일주소
                                                </VTooltip>
                                            </VTextField>
                                        </VCol>
                                    </VRow>
                                    <VRow v-else>
                                        <VCol class="font-weight-bold">이메일</VCol>
                                        <VCol md="8"><span>{{ props.item.email }}</span></VCol>
                                    </VRow>
                                </VCol>
                                <VCol cols="12" md="6">
                                    <VRow no-gutters style="align-items: center;" v-if="isAbleModiy(props.item.id)">
                                        <VCol>웹사이트 URL</VCol>
                                        <VCol md="8">
                                            <VTextField v-model="props.item.website_url" prepend-inner-icon="streamline:browser-website-1-solid"
                                                placeholder="웹사이트 URL 입력해주세요" persistent-placeholder maxlength="250">
                                                <VTooltip activator="parent" location="top">
                                                    하위몰이 없는경우 2차PG사 URL을 입력해주세요.
                                                </VTooltip>
                                            </VTextField>
                                        </VCol>
                                    </VRow>
                                    <VRow v-else>
                                        <VCol class="font-weight-bold">웹사이트 URL</VCol>
                                        <VCol md="8"><span>{{ props.item.website_url }}</span></VCol>
                                    </VRow>
                                </VCol>
                            </VRow>
                        </VCol>
                        <!-- 👉 상위 영업점 수수료율 -->
                        <template v-if="getUserLevel() > 10 && isFixplusAgency() === false">
                            <VDivider/>
                            <VCol cols="12">
                                <VCardTitle>영업점 수수료</VCardTitle>
                            </VCol>
                            <template v-for="i in 6" :key="i">
                                <VCol cols="12" v-if="levels['sales'+(6-i)+'_use'] && getUserLevel() >= getIndexByLevel(6-i)">
                                    <VRow v-if="isAbleModiy(props.item.id)">
                                        <VCol cols="12" md="3">{{ levels['sales'+(6-i)+'_name'] }}/수수료율</VCol>
                                        <VCol cols="12" :md="props.item.id ? 3 : 4">
                                            <VAutocomplete :menu-props="{ maxHeight: 400 }" v-model="props.item['sales'+(6-i)+'_id']"
                                                :items="sales[6-i].value"
                                                :label="levels['sales'+(6-i)+'_name'] + '선택'"
                                                item-title="sales_name" item-value="id" persistent-hint single-line prepend-inner-icon="ph:share-network"
                                                :hint="hintSalesApplyFee(props.item['sales'+(6-i)+'_id'])" :readonly="getUserLevel() <= getIndexByLevel(6-i)"/>

                                                <VTooltip activator="parent" location="top" v-if="props.item['sales'+(6-i)+'_id']">
                                                    {{ sales[6-i].value.find(obj => obj.id === props.item['sales'+(6-i)+'_id'])?.sales_name }}
                                                </VTooltip>
                                        </VCol>
                                        <VCol cols="12" :md="props.item.id ? 2 : 3">
                                            <VTextField v-model="props.item['sales'+(6-i)+'_fee'] " type="number" suffix="%"
                                                :rules="[requiredValidatorV2(props.item['sales'+(6-i)+'_fee'], levels['sales'+(6-i)+'_name']+'수수료율')]" />

                                            <div style="font-size: 0.8em;">
                                                <span style="font-weight: bold;">{{ hintSalesSettleFee(props.item, 6-i) }}</span>
                                            </div>
                                        </VCol>
                                        <FeeChangeBtn v-if="props.item.id" :level=getIndexByLevel(6-i) :item="props.item">
                                        </FeeChangeBtn>
                                    </VRow>
                                    <VRow v-else>
                                        <VCol md="3" class="font-weight-bold">{{ levels['sales'+(6-i)+'_name'] }}/수수료율</VCol>
                                        <VCol md="4">
                                            {{ sales[6-i].value.find(obj => obj.id === props.item['sales'+(6-i)+'_id'])?.sales_name }}
                                        </VCol>
                                        <VCol md="3">
                                            <span>{{ props.item['sales'+(6-i)+'_fee'] }} %</span>
                                        </VCol>
                                    </VRow>
                                </VCol>
                            </template>
                        </template>
                        <VDivider/>
                        <VCol cols="12">
                            <VCardTitle>가맹점 수수료</VCardTitle>
                        </VCol>
                        <VCol cols="12">
                            <VRow v-if="isAbleModiy(props.item.id)">
                                <VCol cols="12" md="3">
                                    가맹점/유보금 수수료율
                                </VCol>
                                    <VCol cols="12" :md="props.item.id ? 3 : 4">
                                        <VTextField v-model="props.item.trx_fee" type="number" suffix="%"
                                            :rules="[requiredValidatorV2(props.item.trx_fee, '가맹점 수수료율')]" v-if="isAbleModiy(props.item.id)"/>
                                    </VCol>
                                    <VCol cols="12" :md="props.item.id ? 2 : 3">
                                        <VTextField v-model="props.item.hold_fee" type="number" suffix="%"
                                            :rules="[requiredValidatorV2(props.item.hold_fee, '가맹점 유보금')]" v-if="isAbleModiy(props.item.id)"  />
                                    </VCol>
                                    <FeeChangeBtn v-if="props.item.id && isAbleModiy(props.item.id)" :level=-1 :item="props.item">
                                    </FeeChangeBtn>
                            </VRow>
                            <VRow v-else>
                                <VCol md="3" class="font-weight-bold">가맹점/유보금/수수료율</VCol>
                                <VCol md="4">
                                    <span>{{ props.item.trx_fee }} %</span>
                                </VCol>
                                <VCol md="4">
                                    <span>{{ props.item.hold_fee }} %</span>
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
    </VRow>
</template>
