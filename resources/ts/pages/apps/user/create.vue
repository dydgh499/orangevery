<script lang="ts" setup>
  import {
businessNumValidator,
emailValidator,
lengthValidatorV2,
passwordValidator,
requiredValidator
} from '@validators';
  
  const email = ref('')
  const password = ref<string>()
  const nickName = ref<string>()
  const reqName = ref<string>()
  const address = ref<string>()
  const fees_rate = ref(0)
    
  const mobile = ref<number>()
  const businessNum = ref<string>()
  const residentNumFirst = ref<string>()
  const residentNumSec = ref<string>()
  //--
  const acctNum = ref<number>()
  const acctNm  = ref<string>()
  const bankbook  = ref<File[]>([])
  const idCard    = ref<File[]>([])
  const contract  = ref<File[]>([])
  //--
  const isPwShow = ref(false)

  const checkbox = ref(false)

  const withdrawalType = ['d+1', 'd+3', 'd+5', 'd+7']
  const salesSegmentType = ['영세', '중소1', '중소2', '중소3']
  const userType = ['가맹점', '대리점', '총판', '지사', '본사']

</script>

<template>
  <VForm @submit.prevent="() => {}">
    <VRow class="match-height">
      <!-- 👉 개인정보 -->
      <VCol cols="12" md="6">
        <VCard>
          <VCardItem>
              <VCardTitle>기본 정보</VCardTitle>
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
                        <VSelect :items="userType" prepend-inner-icon="carbon-skill-level-intermediate" label="등급 선택"/>
                      </VCol>
                  </VRow>
                </VCol>
                <!-- 👉 수수료율 -->
                <VCol cols="12">
                  <VRow no-gutters>
                    <VCol cols="12" md="3">
                      <label for="emailHorizontalIcons">수수료율</label>
                    </VCol>
                    <VCol cols="12" md="9">
                      <VTextField
                        id="emailHorizontalIcons"
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
                        v-model="residentNumFirst"
                        type="number"
                        counter
                        prepend-inner-icon="carbon-identification"
                        placeholder="앞자리 입력"
                        persistent-placeholder
                        style="display: inline-block; width: 50%;"
                        :rules="[requiredValidator, lengthValidatorV2(residentNumFirst, 6)]"                        
                      />
                      <VTextField
                        id="residentSecHorizontalIcons"
                        v-model="residentNumSec"
                        type="number"
                        counter
                        prepend-inner-icon="carbon-identification"
                        placeholder="뒷자리 입력"
                        persistent-placeholder
                        style="display: inline-block; width: 50%;"
                        :rules="[requiredValidator, lengthValidatorV2(residentNumSec, 7)]"
                      />
                    </VCol>
                  </VRow>
                </VCol>
              </VRow>
          </VCardItem>
      </VCard>
      </VCol>      
      <!-- 👉 개인정보 -->
      <VCol cols="12" md="6">
        <VCard>
          <VCardItem>
            <VCardTitle>개인 정보</VCardTitle>
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
                        prepend-inner-icon="ph-buildings"
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
                    <VSelect :items="withdrawalType" prepend-inner-icon="ph-buildings" label="은행 선택"/>
                  </VCol>
                </VRow>
              </VCol>
              <VCol cols="12">
                <VFileInput show-size v-model="bankbook" placeholder="Upload your documents" label="통장 사본 업로드" prepend-icon="tabler-paperclip"/>
                  <VImg max-width="368" :src="bankbook[0].webkitRelativePath" class="auth-illustration mt-16 mb-2"/>
              </VCol>
              <VCol cols="12">
                <VFileInput show-size v-model="idCard" placeholder="Upload your documents" label="신분증 업로드" prepend-icon="tabler-paperclip"/>
              </VCol>
              <VCol cols="12">
                <VFileInput show-size v-model="contract" placeholder="Upload your documents" label="계약서 업로드" prepend-icon="tabler-paperclip"/>
              </VCol>
            </VRow>
          </VCardItem>
        </VCard>
      </VCol>
      <!-- 👉 가맹점정보 -->
      <VCol cols="12" md="6">
        <VCard>
          <VCardItem>
            <VCardTitle>가맹점 정보</VCardTitle>
            <br>
            <VRow>
              <VCol cols="12">
                <VSelect :items="withdrawalType" label="출금 타입 선택"/>
              </VCol>
            </VRow>
          </VCardItem>
        </VCard>
      </VCol>
      <!-- 👉 단말기 정보 -->
      <VCol cols="12" md="6">
        <VCard>
          <VCardItem>
            <VCardTitle>단말기 정보</VCardTitle>
            <br>
            <VRow>
              <VCol cols="12">
                <VSelect :items="withdrawalType" label="출금 타입 선택"/>
              </VCol>
            </VRow>
          </VCardItem>
        </VCard>
      </VCol>      
      <!-- 👉 submit -->
      <VCol>
        <VCard>
          <VCardItem>
            <!-- 👉 submit and reset button -->
            <VCol offset-md="10" cols="12" class="d-flex gap-4">
              <VBtn type="submit">
                Submit
              </VBtn>
              <VBtn color="secondary" type="reset" variant="tonal">
                Reset
              </VBtn>
            </VCol>
          </VCardItem>
        </VCard>
      </VCol>
    </VRow>
  </VForm>
</template>
