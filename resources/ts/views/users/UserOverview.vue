<script lang="ts" setup>
import { requiredValidator, nullValidator, businessNumValidator } from '@validators'
import type { UserPropertie } from '@/views/types'
import FileInput from '@/layouts/utils/FileInput.vue'
import CreateHalfVCol from '@/layouts/utils/CreateHalfVCol.vue'
import SwiperPreview from '@/layouts/utils/SwiperPreview.vue'
import { banks, avatars } from '@/views/users/useStore'
import corp from '@corp'
import { axios } from '@axios'

interface Props {
    item: UserPropertie,
    id: number | string,
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
                acct_num: props.item.acct_num,
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
watchEffect(() => {
    props.item.resident_num = props.item.resident_num_front + props.item.resident_num_back
    console.log(props.item.resident_num)
})

</script>
<template>
    <VRow class="match-height">
        <!-- 👉 개인정보 -->
        <VCol cols="12" md="6">
            <VCard>
                <VCardItem>
                    <VCardTitle>기본정보</VCardTitle>
                    <VRow class="pt-5">
                        <!-- 👉 Email -->
                        <CreateHalfVCol :mdl="3" :mdr="9">
                            <template #name>아이디</template>
                            <template #input>
                                <VTextField type='text' v-model="props.item.user_name" prepend-inner-icon="tabler-mail"
                                    placeholder="아이디 입력" persistent-placeholder :rules="[requiredValidator]"
                                    maxlength="30" />
                            </template>
                        </CreateHalfVCol>
                        <!-- 👉 Password -->
                        <CreateHalfVCol :mdl="3" :mdr="9" v-if="props.id == 0">
                            <template #name>패스워드</template>
                            <template #input>
                                <VTextField v-model="props.item.user_pw" counter prepend-inner-icon="tabler-lock"
                                    :rules="[requiredValidator]"
                                    :append-inner-icon="is_show ? 'tabler-eye' : 'tabler-eye-off'"
                                    :type="is_show ? 'text' : 'password'" persistent-placeholder
                                    @click:append-inner="is_show = !is_show" autocomplete="new-password" />
                            </template>
                        </CreateHalfVCol>
                        <!-- 👉 대표자명 -->
                        <CreateHalfVCol :mdl="3" :mdr="9">
                            <template #name>대표자명</template>
                            <template #input>
                                <VTextField id="nickNameHorizontalIcons" v-model="props.item.nick_name"
                                    prepend-inner-icon="tabler-user" placeholder="대표자명 입력" persistent-placeholder />
                            </template>
                        </CreateHalfVCol>
                        <!-- 👉 Address -->
                        <CreateHalfVCol :mdl="3" :mdr="9">
                            <template #name>주소</template>
                            <template #input>
                                <VTextField id="addressHorizontalIcons" v-model="props.item.addr"
                                    prepend-inner-icon="tabler-map-pin" placeholder="주소 입력" persistent-placeholder
                                    maxlength="200" />
                            </template>
                        </CreateHalfVCol>
                        <!-- 👉 Mobile -->
                        <CreateHalfVCol :mdl="3" :mdr="9">
                            <template #name>휴대폰번호</template>
                            <template #input>
                                <VTextField id="mobileHorizontalIcons" v-model="props.item.phone_num" type="text"
                                    prepend-inner-icon="tabler-device-mobile" placeholder="010-0000-0000"
                                    persistent-placeholder maxlength="13" />
                            </template>
                        </CreateHalfVCol>
                        <!-- 👉 사업자등록번호 -->
                        <CreateHalfVCol :mdl="3" :mdr="9">
                            <template #name>사업자등록번호</template>
                            <template #input>
                                <VTextField id="businessHorizontalIcons" v-model="props.item.business_num" type="text"
                                    prepend-inner-icon="ic-outline-business-center" placeholder="123-12-12345"
                                    persistent-placeholder :rules="[businessNumValidator]">
                                    <VTooltip activator="parent" location="top" v-if="corp.use_different_settlement">
                                        {{ "사업자번호를 입력하지 않거나, 정확하게 입력하지 않으면 차액정산대상에서 제외됩니다." }}
                                    </VTooltip>
                                </VTextField>
                            </template>
                        </CreateHalfVCol>
                        <!-- 👉 주민등록 번호 -->
                        <CreateHalfVCol :mdl="3" :mdr="9">
                            <template #name>주민등록번호</template>
                            <template #input>
                                <VRow style="align-items: center;">
                                    <VCol :cols="5">
                                        <VTextField v-model="props.item.resident_num_front" type="text"
                                            prepend-inner-icon="carbon-identification" placeholder="800101" maxlength="6" />
                                    </VCol>
                                    <span> - </span>
                                    <VCol :cols="5">
                                        <VTextField v-model="props.item.resident_num_back" placeholder="*******" maxlength="7"
                                            :append-inner-icon="is_resident_num_back_show ? 'tabler-eye' : 'tabler-eye-off'"
                                            :type="is_resident_num_back_show ? 'text' : 'password'" 
                                            @click:append-inner="is_resident_num_back_show = !is_resident_num_back_show" />
                                    </VCol>
                                </VRow>
                            </template>
                        </CreateHalfVCol>
                    </VRow>
                </VCardItem>
                <VCardItem>
                    <VCardTitle>은행정보</VCardTitle>
                    <VRow class="pt-5">
                        <CreateHalfVCol :mdl="3" :mdr="9">
                            <template #name>계좌번호</template>
                            <template #input>
                                <VTextField id="acctNumHorizontalIcons" v-model="props.item.acct_num"
                                    prepend-inner-icon="ri-bank-card-fill" placeholder="계좌번호 입력" persistent-placeholder />
                            </template>
                        </CreateHalfVCol>
                        <CreateHalfVCol :mdl="3" :mdr="9">
                            <template #name>예금주</template>
                            <template #input>
                                <VTextField id="acctNmHorizontalIcons" v-model="props.item.acct_name"
                                    prepend-inner-icon="tabler-user" placeholder="예금주 입력" persistent-placeholder />
                            </template>
                        </CreateHalfVCol>
                        <CreateHalfVCol :mdl="3" :mdr="9">
                            <template #name>은행</template>
                            <template #input>
                                <VAutocomplete :menu-props="{ maxHeight: 400 }" v-model="props.item.acct_bank_code"
                                    :items="[{ code: null, title: '선택안함' }].concat(banks)" prepend-inner-icon="ph-buildings"
                                    label="은행 선택" item-title="title" item-value="code" persistent-hint single-line
                                    :hint="`${props.item.acct_bank_name}, 은행 코드: ${props.item.acct_bank_code ? props.item.acct_bank_code : '000'} `"
                                    :rules="[nullValidator]" @update:modelValue="setAcctBankName()" />
                            </template>
                        </CreateHalfVCol>
                        <VCol cols="12" v-if="corp.pv_options.paid.use_acct_verification">
                            <VBtn @click="onwerCheck" prepend-icon="ri:pass-valid-line" class="float-right">
                                예금주 검증
                            </VBtn>
                        </VCol>
                    </VRow>
                </VCardItem>
                <VCardItem>
                    <VCardTitle>프로필 이미지</VCardTitle>
                    <VRow class="pt-5">
                        <VCol cols="12">
                            <VRow no-gutters>
                                <SwiperPreview :items="avatars"
                                    :default_img="props.item.profile_img ?? avatars[Math.floor(Math.random() * avatars.length)]"
                                    :item_name="'프로필'" :lmd="10" :rmd="2" @update:file="props.item.profile_file = $event"
                                    @update:default="props.item.profile_img = $event">
                                </SwiperPreview>
                            </VRow>
                        </VCol>
                    </VRow>
                </VCardItem>
            </VCard>
        </VCol>
        <!-- 👉 계약정보 -->
        <VCol cols="12" md="6">
            <VCard>
                <VCardItem>
                    <VCardTitle>계약파일</VCardTitle>
                    <VRow class="pt-5">
                        <VCol cols="12">
                            <VRow no-gutters>
                                <FileInput :label="`통장사본 업로드`"
                                    :preview="props.item.passbook_img ?? '/utils/icons/img-preview.svg'"
                                    @update:file="props.item.passbook_file = $event" />
                            </VRow>
                        </VCol>
                        <VCol cols="12">
                            <VRow no-gutters>
                                <FileInput :label="`신분증 업로드`" :preview="props.item.id_img ?? '/utils/icons/img-preview.svg'"
                                    @update:file="props.item.id_file = $event" />
                            </VRow>
                        </VCol>
                        <VCol cols="12">
                            <VRow no-gutters>
                                <FileInput :label="`계약서 업로드`"
                                    :preview="props.item.contract_img ?? '/utils/icons/img-preview.svg'"
                                    @update:file="props.item.contract_file = $event" />
                            </VRow>
                        </VCol>
                        <VCol cols="12">
                            <VRow no-gutters>
                                <FileInput :label="`사업자 등록증 업로드`"
                                    :preview="props.item.bsin_lic_img ?? '/utils/icons/img-preview.svg'"
                                    @update:file="props.item.bsin_lic_file = $event" />
                            </VRow>
                        </VCol>
                    </VRow>
                </VCardItem>
            </VCard>
        </VCol>
    </VRow>
</template>
