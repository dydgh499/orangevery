<script lang="ts" setup>

import { businessNumValidator, emailValidator, lengthValidatorV2, passwordValidator, requiredValidator } from '@validators';
import type { UserPropertie } from '@/views/types'

interface Props {
    item: UserPropertie,
    id: number,
}
const props = defineProps<Props>();
const banks = [
  {code: "001", title: "한국은행"}, {code: "002", title: "산업은행"}, {code: "003", title: "기업은행"}, 
  {code: "004", title: "국민은행"}, {code: "005", title: "외환은행"}, {code: "007", title: "수협은행"}, 
  {code: "008", title: "수출입은행"}, {code: "011", title: "농협은행"}, {code: "012", title: "농협회원조합"}, 
  {code: "020", title: "우리은행"}, {code: "023", title: "SC제일은행"}, {code: "026", title: "서울은행"},
  {code: "027", title: "한국씨티은행"}, {code: "031", title: "대구은행"}, {code: "032", title: "부산은행"}, 
  {code: "034", title: "광주은행"}, {code: "035", title: "제주은행"}, {code: "037", title: "전북은행"}, 
  {code: "039", title: "경남은행"}, {code: "045", title: "새마을금고연합회"}, {code: "048", title: "신협중앙회"}, 
  {code: "050", title: "상호저축은행"}, {code: "051", title: "기타 외국계은행"}, {code: "052", title: "모건스탠리은행"},
  {code: "054", title: "HSBC은행"}, {code: "055", title: "도이치은행"}, {code: "056", title: "알비에스피엘씨은행"}, 
  {code: "057", title: "제이피모간체이스은행"}, {code: "058", title: "미즈호코퍼레이트은행"}, {code: "059", title: "미쓰비시도쿄UFJ은행"}, 
  {code: "060", title: "BOA"}, {code: "061", title: "비엔피파리바은행"}, {code: "062", title: "중국공상은행"}, 
  {code: "063", title: "중국은행"}, {code: "064", title: "산림조합"}, {code: "065", title: "대화은행"},
  {code: "071", title: "우체국"}, {code: "076", title: "신용보증기금"}, {code: "077", title: "기술신용보증기금"}, 
  {code: "081", title: "하나은행"}, {code: "088", title: "신한은행"}, {code: "089", title: "케이뱅크"}, 
  {code: "090", title: "카카오뱅크"}, {code: "092", title: "토스뱅크"}, {code: "238", title: "(구)미래에셋증권"},
]
//--
const is_show = ref(false)
const bank = ref({ code: props.item.acct_bank_cd, title: props.item.acct_bank_nm})
const passbook_img  = {file: ref<File[]>(), priview: ref<string>(props.item.passbook_img), class: ref<string>(), label: '통장 사본 업로드', visable: ref(false)}
const id_img        = {file: ref<File[]>(), priview: ref<string>(props.item.id_img), class: ref<string>(), label: '신분증 업로드', visable: ref(false)}
const contract_img  = {file: ref<File[]>(), priview: ref<string>(props.item.contract_img), class: ref<string>(), label: '계약서 업로드', visable: ref(false)}
const bsin_lic_img  = {file: ref<File[]>(), priview: ref<string>(props.item.bsin_lic_img), class: ref<string>(), label: '사업자 등록증 업로드', visable: ref(false)}
  
watch(passbook_img.file, (newFile) => {
  let isClear = newFile == null || newFile?.length == 0
  passbook_img.priview.value = isClear ? '/images/img-preview.svg' : URL.createObjectURL(newFile![0])
  passbook_img.class.value = isClear ? 'h-0' : 'preview-wrap'
})
watch(id_img.file, (newFile) => {  
  let isClear = newFile == null || newFile?.length == 0
  id_img.priview.value = isClear ? '/images/img-preview.svg' : URL.createObjectURL(newFile![0])
  id_img.class.value = isClear ? 'h-0' : 'preview-wrap'
})
watch(contract_img.file, (newFile) => {
  let isClear = newFile == null || newFile?.length == 0
  contract_img.priview.value = isClear ? '/images/img-preview.svg' : URL.createObjectURL(newFile![0])
  contract_img.class.value = isClear ? 'h-0' : 'preview-wrap'
})
watch(bsin_lic_img.file, (newFile) => {
  let isClear = newFile == null || newFile?.length == 0
  bsin_lic_img.priview.value = isClear ? '/images/img-preview.svg' : URL.createObjectURL(newFile![0])
  bsin_lic_img.class.value = isClear ? 'h-0' : 'preview-wrap'
})
function zoomIn(file: any) {
    if(file.priview.value !='/images/img-preview.svg') 
        file.visable.value = !file.visable.value
}

const selectedOption = ref({ state: 'Florida', abbr: 'FL' })

const items = [
  { state: 'Florida', abbr: 'FL' },
  { state: 'Georgia', abbr: 'GA' },
  { state: 'Nebraska', abbr: 'NE' },
  { state: 'California', abbr: 'CA' },
  { state: 'New York', abbr: 'NY' },
]
</script>
<style lang="scss">
  .preview {
    border: 2px solid rgb(238, 238, 238);
    border-radius: 0.5em;
    margin-block: 0;
    margin-inline: 0.5em;
  }

  .preview:hover {
    border: 2px solid rgb(200, 200, 200);
    cursor: pointer;
  }

  @media only screen and (max-device-width: 960px) {
    .preview {
      margin-block: 0.5em;
      margin-inline: 0;
    }
  }
</style>
<template>
<VRow class="match-height">
<!-- 👉 개인정보 -->
<VCol cols="12" md="6">
    <VCard>
        <VCardItem>
            <VCardTitle>기본정보</VCardTitle>
            <VRow class="pt-5">
            <!-- 👉 Email -->
            <VCol cols="12">
                <VRow no-gutters>
                <VCol cols="12" md="3">
                    <label for="emailHorizontalIcons">이메일</label>
                </VCol>

                <VCol cols="12" md="9">
                    <VTextField
                    id="emailHorizontalIcons"
                    v-model="props.item.user_name"
                    prepend-inner-icon="tabler-mail"
                    placeholder="ID로 사용됩니다."
                    persistent-placeholder
                    :rules="[requiredValidator, emailValidator]"
                    />
                </VCol>
                </VRow>
            </VCol>
            <!-- 👉 Password -->
            <VCol cols="12" v-show="props.id == 0">
                <VRow no-gutters>
                <VCol cols="12" md="3">
                    <label for="passwordHorizontalIcons">패스워드</label>
                </VCol>

                <VCol cols="12" md="9">
                    <VTextField
                        id="passwordHorizontalIcons"
                        v-model="props.item.user_pw"
                        counter
                        prepend-inner-icon="tabler-lock"
                        :rules="[requiredValidator, passwordValidator]"
                        :append-inner-icon="is_show ? 'tabler-eye' : 'tabler-eye-off'"
                        :type="is_show ? 'text' : 'password'"                        
                        placeholder="소문자,대문자,특수문자로 이루어진 8자 이상 문자열"
                        persistent-placeholder
                        @click:append-inner="is_show = !is_show"
                        autocomplete
                    />
                </VCol>
                </VRow>
            </VCol>
            <!-- 👉 대표자명 -->
            <VCol cols="12">
                <VRow no-gutters>
                <VCol cols="12" md="3">
                    <label for="nickNameHorizontalIcons">대표자명</label>
                </VCol>
                <VCol cols="12" md="9">
                    <VTextField
                    id="nickNameHorizontalIcons"
                    v-model="props.item.nick_name"
                    prepend-inner-icon="tabler-user"
                    placeholder="사용자명으로 사용됩니다."
                    persistent-placeholder
                    />
                </VCol>
                </VRow>
            </VCol>
            <!-- 👉 Address -->
            <VCol cols="12">
                <VRow no-gutters>
                <VCol cols="12" md="3">
                    <label for="addressHorizontalIcons">주소</label>
                </VCol>
                <VCol cols="12" md="9">
                    <VTextField
                    id="addressHorizontalIcons"
                    v-model="props.item.addr"
                    prepend-inner-icon="tabler-map-pin"
                    placeholder="주소 입력"
                    persistent-placeholder
                    />
                </VCol>
                </VRow>
            </VCol>            
            <!-- 👉 Mobile -->
            <VCol cols="12">
                <VRow no-gutters>
                <VCol cols="12" md="3">
                    <label for="mobileHorizontalIcons">휴대폰번호</label>
                </VCol>

                <VCol cols="12" md="9">
                    <VTextField
                    id="mobileHorizontalIcons"
                    v-model="props.item.phone_num"
                    type="number"
                    prepend-inner-icon="tabler-device-mobile"
                    placeholder="숫자만 입력해주세요."
                    persistent-placeholder
                    />
                </VCol>
                </VRow>
            </VCol>
            <!-- 👉 사업자 번호 -->
            <VCol cols="12">
                <VRow no-gutters>
                <VCol cols="12" md="3">
                    <label for="businessHorizontalIcons">사업자번호</label>
                </VCol>

                <VCol cols="12" md="9">
                    <VTextField
                    id="businessHorizontalIcons"
                    v-model="props.item.business_num"
                    type="number"
                    prepend-inner-icon="ic-outline-business-center"
                    placeholder="숫자만 입력해주세요."
                    persistent-placeholder
                    :rules="[requiredValidator, businessNumValidator(props.item.business_num)]"
                    />
                </VCol>
                </VRow>
            </VCol>
            <!-- 👉 주민등록 번호 -->
            <VCol cols="12">
                <VRow no-gutters>
                <VCol cols="12" md="3">
                    <label for="residentHorizontalIcons">주민등록번호</label>
                </VCol>
                <VCol cols="12" md="9" class="">
                    <VTextField
                    id="residentFirstHorizontalIcons"
                    v-model="props.item.resident_num"
                    type="text"
                    counter
                    prepend-inner-icon="carbon-identification"
                    placeholder="앞자리 입력"
                    persistent-placeholder
                    :rules="[requiredValidator, lengthValidatorV2(props.item.resident_num, 13)]"
                    maxlength="13"
                    />
                </VCol>
                </VRow>
            </VCol>
            </VRow>
        </VCardItem>
        <VCardItem>
            <VCardTitle>은행정보</VCardTitle>
            <VRow class="pt-5">
                <VCol cols="12">
                    <VRow no-gutters>
                        <VCol cols="12" md="3">
                        <label for="acctNumHorizontalIcons">계좌번호</label>
                        </VCol>
                        <VCol cols="12" md="9">
                        <VTextField
                            id="acctNumHorizontalIcons"
                            type="number"
                            v-model="props.item.acct_num"
                            prepend-inner-icon="ri-bank-card-fill"
                            placeholder="계좌번호 입력"
                            persistent-placeholder
                        />
                        </VCol>
                    </VRow>
                </VCol>
                <VCol cols="12">
                    <VRow no-gutters>
                        <VCol cols="12" md="3">
                        <label for="acctNmHorizontalIcons">예금주</label>
                        </VCol>
                        <VCol cols="12" md="9">
                        <VTextField
                            id="acctNmHorizontalIcons"
                            v-model="props.item.acct_nm"
                            prepend-inner-icon="tabler-user"
                            placeholder="예금주 입력"
                            persistent-placeholder
                        />
                        </VCol>
                    </VRow>
                </VCol>
                <VCol cols="12">
                    <VRow no-gutters>
                        <VCol cols="12" md="3">
                            <label for="acctNmHorizontalIcons">은행</label>
                        </VCol>
                        <VCol cols="12" md="9">
                            <VAutocomplete :menu-props="{ maxHeight: 400 }"
                                v-model="bank" 
                                :items="banks" 
                                prepend-inner-icon="ph-buildings" 
                                label="은행 선택"
                                :hint="`${bank.title}, 은행 코드: ${bank.code} `"
                                item-title="title" 
                                item-value="code"
                                persistent-hint 
                                return-object
                                single-line
                            />
                        </VCol>
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
        <VCol cols="12" v-for="file in [passbook_img, id_img, contract_img, bsin_lic_img]" :key=file.label>
            <VRow no-gutters>
            <VCol cols="12" md="9">
                <VFileInput accept="image/*" show-size v-model="file.file.value" :label="file.label" prepend-icon="tabler-paperclip"/>
            </VCol>
            <VCol cols="12" md="3">
                <VImg rounded :src="file.priview.value" class="preview" @click="zoomIn(file)" style="height: 163px;"/>
                <VDialog
                    v-model="file.visable.value"
                >
                    <!-- Dialog close btn -->
                    <DialogCloseBtn @click="file.visable.value = !file.visable.value" />
                    <!-- Dialog Content -->
                    <VCard>
                    <VImg rounded :src="file.priview.value" style='width: 100%;'></VImg>
                    </VCard>
                </VDialog>
            </VCol>
            </VRow>
        </VCol>          
        </VRow>
    </VCardItem>            
    </VCard>
</VCol>
</VRow>
</template>

  
