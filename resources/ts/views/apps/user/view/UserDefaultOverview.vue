<script lang="ts" setup>

import type { IUserCreate } from '@/views/apps/user/types';
import axios from '@axios';
import { businessNumValidator, emailValidator, lengthValidatorV2, passwordValidator, requiredValidator } from '@validators';
import { VForm } from 'vuetify/components';

interface Props {
  user?: IUserCreate
  submit: {text: string}
}

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
const userTypes = [
  {code: 0, title: "가맹점"},
  {code: 10, title: "대리점"},
  {code: 20, title: "총판"},
  {code: 30, title: "지사"},
  {code: 40, title: "본사"},
]

const props = withDefaults(defineProps<Props>(), {})

console.log(props.user)

const userVForm = ref<VForm>()
//--
const userType = ref({code:0, title: "가맹점"})
const email = ref<string>(props.user?.email)
const password = ref<string>()
const nickName = ref<string>()
const reqName = ref<string>()
const address = ref<string>()
const fees_rate = ref(0.0)
const isPwShow = ref(false)
//--    props.user?.acctNm
const mobile = ref<number>()
const businessNum = ref<string>(props.user?.residentNum || '')
const residentNum = ref<string>(props.user?.residentNum || '')
//--
const acctNum = ref<number>()
const acctNm  = ref<string>()
const bank = ref({ code: '000', title: '은행명' })
const bankbook = {file: ref<File[]>(), priview: ref<string>(), class: ref<string>(), label: '통장 사본 업로드'}
const idCard = {file: ref<File[]>(), priview: ref<string>(), class: ref<string>(), label: '신분증 업로드'}
const contact = {file: ref<File[]>(), priview: ref<string>(), class: ref<string>(), label: '계약서 업로드'}
//--
const submit = {text: '제출', isCreate: true,}
//--
watch(bankbook.file, (newFile) => {
  let isClear = newFile == null || newFile?.length == 0
  bankbook.priview.value = isClear ? '' : URL.createObjectURL(newFile![0])
  bankbook.class.value = isClear ? 'h-0' : 'preview-wrap'
})
watch(idCard.file, (newFile) => {  
  let isClear = newFile == null || newFile?.length == 0
  idCard.priview.value = isClear ? '' : URL.createObjectURL(newFile![0])
  idCard.class.value = isClear ? 'h-0' : 'preview-wrap'
})
watch(contact.file, (newFile) => {
  let isClear = newFile == null || newFile?.length == 0
  contact.priview.value = isClear ? '' : URL.createObjectURL(newFile![0])
  contact.class.value = isClear ? 'h-0' : 'preview-wrap'
})
//--
const userSave = async () => {
  let isValid = await userVForm.value?.validate();
  if(isValid)
  {
    let params = {
      userType  : userType.value,
      email     : email.value,
      password  : password.value,
      nickName  : nickName.value,
      reqName   : reqName.value,
      address   : address.value,
      fees_rate : fees_rate.value,
      mobile    : mobile.value,
      businessNum  : businessNum.value,
      residentNum  : residentNum.value[0] + residentNum.value[1],
      acctNum   : acctNum.value,
      acctNm    : acctNm.value,
      bank      : bank.value,
      bankbook  : bankbook.file.value,
      idCard  : idCard.file.value,
      contact  : contact.file.value,
    }
    let result = await axios.post('/api/v1/manager/user/create', params)
    if(result.status = 200)
    {

    }
    else
    {
      
    }
  }
}
</script>
<style lang="scss">
  .preview-wrap {
    padding: 20px;

    .preview {
      border: 2px solid rgb(238, 238, 238);
      border-radius: 0.5em;
    }
  }
</style>
<template>
    <VForm @submit.prevent="userSave" ref="userVForm" id="userForm">
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
                            v-model="email"
                            prepend-inner-icon="tabler-mail"
                            placeholder="ID로 사용됩니다."
                            persistent-placeholder
                            :rules="[requiredValidator, emailValidator]"
                          />
                        </VCol>
                      </VRow>
                    </VCol>
                    <!-- 👉 Password -->
                    <VCol cols="12">
                      <VRow no-gutters>
                        <VCol cols="12" md="3">
                          <label for="passwordHorizontalIcons">패스워드</label>
                        </VCol>

                        <VCol cols="12" md="9">
                          <VTextField
                            id="passwordHorizontalIcons"
                            v-model="password"
                            counter
                            prepend-inner-icon="tabler-lock"
                            :rules="[requiredValidator, passwordValidator]"
                            :append-inner-icon="isPwShow ? 'tabler-eye' : 'tabler-eye-off'"
                            :type="isPwShow ? 'text' : 'password'"                        
                            placeholder="소문자,대문자,특수문자로 이루어진 8자 이상 문자열"
                            persistent-placeholder
                            @click:append-inner="isPwShow = !isPwShow"
                            autocomplete
                          />
                        </VCol>
                      </VRow>
                    </VCol>    
                    <!-- 👉 유저타입 -->
                    <VCol cols="12">
                      <VRow no-gutters>
                          <VCol cols="12" md="3">
                            <label for="acctNmHorizontalIcons">유저종류</label>
                          </VCol>
                          <VCol cols="12" md="9">
                            <VSelect :items="userTypes" prepend-inner-icon="carbon-skill-level-intermediate" label="등급 선택"
                            v-model="userType" item-title="title" item-value="code"
                            persistent-hint return-object single-line
                            />
                          </VCol>
                      </VRow>
                    </VCol>
                    <!-- 👉 수수료율 -->
                    <VCol cols="12">
                      <VRow no-gutters>
                        <VCol cols="12" md="3">
                          <label for="feesRateHorizontalIcons">수수료율</label>
                        </VCol>
                        <VCol cols="12" md="9">
                          <VTextField
                            id="feesRateHorizontalIcons"
                            prepend-inner-icon="tabler-currency-won"
                            v-model="fees_rate"
                            label="수수료율"
                            type="number"
                            suffix="%"
                          />
                        </VCol>
                      </VRow>
                    </VCol>                
                    <VDivider/>
                    <!-- 👉 대표자명 -->
                    <VCol cols="12">
                      <VRow no-gutters>
                        <VCol cols="12" md="3">
                          <label for="nickNameHorizontalIcons">대표자명</label>
                        </VCol>
                        <VCol cols="12" md="9">
                          <VTextField
                            id="nickNameHorizontalIcons"
                            v-model="nickName"
                            prepend-inner-icon="tabler-user"
                            placeholder="사용자명으로 사용됩니다."
                            persistent-placeholder
                          />
                        </VCol>
                      </VRow>
                    </VCol>
                    <!-- 👉 상호 -->
                    <VCol cols="12">
                      <VRow no-gutters>
                        <VCol cols="12" md="3">
                          <label for="reqNameHorizontalIcons">상호</label>
                        </VCol>
                        <VCol cols="12" md="9">
                          <VTextField
                            id="reqNameHorizontalIcons"
                            v-model="reqName"
                            prepend-inner-icon="ph-buildings"
                            placeholder="상호 입력"
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
                            v-model="address"
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
                            v-model="mobile"
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
                            v-model="businessNum"
                            type="number"
                            prepend-inner-icon="ic-outline-business-center"
                            placeholder="숫자만 입력해주세요."
                            persistent-placeholder
                            :rules="[requiredValidator, businessNumValidator(businessNum)]"
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
                            v-model="residentNum"
                            type="text"
                            counter
                            prepend-inner-icon="carbon-identification"
                            placeholder="앞자리 입력"
                            persistent-placeholder
                            :rules="[requiredValidator, lengthValidatorV2(residentNum, 13)]"
                            maxlength="13"
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
              <VCardTitle>계약정보</VCardTitle>
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
                          v-model="acctNum"
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
                          v-model="acctNm"
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
                      <VSelect :items="banks" prepend-inner-icon="ph-buildings" label="은행 선택"
                      v-model="bank" :hint="`${bank.title}, 은행 코드: ${bank.code} `"
                      item-title="title" item-value="code"
                      persistent-hint return-object single-line
                      />
                    </VCol>
                  </VRow>
                </VCol> 
                <VDivider/>
                <VCol cols="12" v-for="file in [bankbook, idCard, contact]" :key=file.label>
                  <VFileInput accept="image/*" show-size v-model="file.file.value" :label="file.label" prepend-icon="tabler-paperclip"/>
                  <div :class="`${file.class.value}`">
                    <VImg rounded :src="file.priview.value" class="mx-auto preview"/>
                  </div>
                </VCol>          
              </VRow>
            </VCardItem>
            
            <VCol class="d-flex gap-4">
                <VBtn type="submit" style="margin-left: auto;">
                  {{ submit.text }}
                </VBtn>
                <VBtn color="secondary" @click="userVForm?.reset()" variant="tonal">
                  리셋
                </VBtn>            
              </VCol>    
          </VCard>
        </VCol>
        <!-- 👉 submit -->
      </VRow>
    </VForm>    
</template>

  
