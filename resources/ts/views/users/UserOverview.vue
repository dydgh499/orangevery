<script lang="ts" setup>
import FileInput from '@/layouts/utils/FileInput.vue'
import SwiperPreview from '@/layouts/utils/SwiperPreview.vue'
import type { UserPropertie } from '@/views/types'
import { avatars, banks, getUserIdValidate, getUserPasswordValidate } from '@/views/users/useStore'
import { axios, getUserLevel, isAbleModiy } from '@axios'
import corp from '@corp'

interface Props {
    item: UserPropertie,
    id: number | string,
    is_mcht: boolean,
}
const props = defineProps<Props>()
const alert = <any>(inject('alert'))
const snackbar = <any>(inject('snackbar'))
const errorHandler = <any>(inject('$errorHandler'))
const is_show = ref(false)
const is_resident_num_back_show = ref(false)

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

const idRules = computed(() => {
    return getUserIdValidate(props.is_mcht ? 0 : 1, props.item.user_name)
})

const passwordRules = computed(() => {
    return getUserPasswordValidate(props.is_mcht ? 0 : 1, props.item.user_pw)
})

watchEffect(() => {
    props.item.resident_num = props.item.resident_num_front + props.item.resident_num_back
})
</script>
<template>
    <VRow class="match-height">
        <!-- 👉 개인정보 -->
        <VCol cols="12" md="6">
            <VCard>
                <VCardItem>
                    <VCardTitle>기본정보</VCardTitle>
                    <VRow class="pt-3">
                        <VCol cols="12" md="6">
                            <VRow no-gutters v-if="isAbleModiy(props.item.id)">
                                <VCol>
                                    <label>* 아이디</label>
                                </VCol>
                                <VCol md="8">
                                    <VTextField type='text' v-model="props.item.user_name" prepend-inner-icon="tabler-mail"
                                        placeholder="아이디 입력" persistent-placeholder :rules="idRules"
                                        maxlength="30"/>
                                </VCol>
                            </VRow>
                            <VRow v-else>
                                <VCol class="font-weight-bold">아이디</VCol>
                                <VCol md="8"><span>{{ props.item.user_name }}</span></VCol>
                            </VRow>
                        </VCol>
                        <VCol cols="12" md="6" v-if="props.id == 0">
                            <VRow no-gutters>
                                <VCol>
                                    <label>* 패스워드</label>
                                </VCol>
                                <VCol md="8">
                                    <VTextField v-model="props.item.user_pw" counter prepend-inner-icon="tabler-lock"
                                    :rules="passwordRules"
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
                                    <label>대표자 연락처</label>
                                </VCol>
                                <VCol md="8">
                                    <VTextField v-model="props.item.phone_num" type="text"
                                    prepend-inner-icon="tabler-device-mobile" placeholder="010-0000-0000"
                                    persistent-placeholder maxlength="13" />                                    
                                </VCol>
                            </VRow>
                            <VRow v-else>
                                <VCol class="font-weight-bold">대표자 연락처</VCol>
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
                                <VCol>
                                    <label>주민등록번호</label>
                                </VCol>
                                <VCol md="10">
                                    <VRow style="align-items: center;">
                                        <VCol :cols="5">
                                            <VTextField v-model="props.item.resident_num_front" type="number" id="regidentFrontNum"
                                                prepend-inner-icon="carbon-identification" placeholder="800101" maxlength="6"/>
                                        </VCol>
                                        <span> - </span>
                                        <VCol :cols="5">
                                            <VTextField v-model="props.item.resident_num_back" placeholder="*******" id="regidentBackNum"
                                                maxlength="7"
                                                :append-inner-icon="is_resident_num_back_show ? 'tabler-eye' : 'tabler-eye-off'"
                                                :type="is_resident_num_back_show ? 'number' : 'password'"
                                                @click:append-inner="is_resident_num_back_show = !is_resident_num_back_show" />
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
                                    prepend-inner-icon="ri-bank-card-fill" placeholder="계좌번호 입력" persistent-placeholder maxlength="20" />
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
                                <VCol>
                                    <label>은행</label>
                                </VCol>
                                <VCol md="8">
                                    <VAutocomplete :menu-props="{ maxHeight: 400 }" v-model="props.item.acct_bank_code"
                                    :items="[{ code: null, title: '선택안함' }].concat(banks)" prepend-inner-icon="ph-buildings"
                                    label="은행 선택" item-title="title" item-value="code" persistent-hint single-line
                                    :hint="`${props.item.acct_bank_name}, 은행 코드: ${props.item.acct_bank_code ? props.item.acct_bank_code : '000'} `" 
                                    @update:modelValue="setAcctBankName()" />
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
                <VCardItem>
                    <VCardTitle>프로필 이미지</VCardTitle>
                    <VRow class="pt-5">
                        <VCol cols="12">
                            <VRow no-gutters>
                                <SwiperPreview :items="avatars"
                                    :preview="props.item.profile_img ?? avatars[Math.floor(Math.random() * avatars.length)]"
                                    :label="'프로필'" :lmd="10" :rmd="2" @update:file="props.item.profile_file = $event"
                                    @update:path="props.item.profile_img = $event">
                                </SwiperPreview>
                            </VRow>
                        </VCol>
                    </VRow>
                </VCardItem>
            </VCard>
        </VCol>
        <!-- 👉 계약정보 -->
        <VCol cols="12" md="6" v-if="getUserLevel() >= 35 || corp.id !== 8">
            <VCard>
                <VCardItem>
                    <VCardTitle>계약파일</VCardTitle>
                    <VRow class="pt-5">
                        <VCol cols="12" md=6>
                            <VRow no-gutters>
                                <FileInput :label="`통장사본 업로드`"
                                    :preview="props.item.passbook_img ? props.item.passbook_img : '/utils/icons/img-preview.svg'"
                                    @update:file="props.item.passbook_file = $event"
                                    @update:path="props.item.passbook_img = $event" />
                            </VRow>
                        </VCol>
                        <VCol cols="12" md=6>
                            <VRow no-gutters>
                                <FileInput :label="`신분증 업로드`"
                                    :preview="props.item.id_img ? props.item.id_img : '/utils/icons/img-preview.svg'"
                                    @update:file="props.item.id_file = $event" @update:path="props.item.id_img = $event" />
                            </VRow>
                        </VCol>
                        <VCol cols="12" md=6>
                            <VRow no-gutters>
                                <FileInput :label="`계약서 업로드`"
                                    :preview="props.item.contract_img ? props.item.contract_img : '/utils/icons/img-preview.svg'"
                                    @update:file="props.item.contract_file = $event"
                                    @update:path="props.item.contract_img = $event" />
                            </VRow>
                        </VCol>
                        <VCol cols="12" md=6>
                            <VRow no-gutters>
                                <FileInput :label="`사업자 등록증 업로드`"
                                    :preview="props.item.bsin_lic_img ? props.item.bsin_lic_img : '/utils/icons/img-preview.svg'"
                                    @update:file="props.item.bsin_lic_file = $event"
                                    @update:path="props.item.bsin_lic_img = $event" />
                            </VRow>
                        </VCol>
                    </VRow>
                </VCardItem>
                <template v-if="corp.pv_options.paid.use_syslink">
                    <div style="display: flex; margin-left: 2em;">
                        <span>SYSLINK 연동여부</span>
                        <span style="margin-left: 1em;">
                            <VSwitch hide-details v-model="props.item.use_syslink" color="primary" />
                        </span>

                    </div>
                    <VCardItem v-if="props.is_mcht && props.id">
                        <VCardTitle>SYSLINK 연동정보</VCardTitle>
                        <span :class="props.item?.syslink?.code === 'SUCCESS' ? 'text-success' : 'text-error'">{{ props.item?.syslink?.message }}</span>
                    </VCardItem>

                </template>
            </VCard>
        </VCol>
    </VRow>
</template>
