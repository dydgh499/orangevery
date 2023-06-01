<script lang="ts" setup>

import { businessNumValidator, emailValidator, lengthValidatorV2, passwordValidator, requiredValidator } from '@validators';
import type { UserPropertie } from '@/views/types'
import FileInput from '@/layouts/utils/FileInput.vue';
import CreateHalfVCol from '@/layouts/utils/CreateHalfVCol.vue';
import { banks } from '@/views/users/useStore';
interface Props {
    item: UserPropertie,
    id: number | string,
}
const props = defineProps<Props>();

const is_show = ref(false)
const bank  = ref({ code: props.item.acct_bank_cd, title: props.item.acct_bank_nm })

watchEffect(() => {
    props.item.acct_bank_cd = bank.value.code
    props.item.acct_bank_nm = bank.value.title
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
                                <VTextField v-model="props.item.user_name" prepend-inner-icon="tabler-mail"
                                    placeholder="ID로 사용됩니다." persistent-placeholder
                                    :rules="[requiredValidator, emailValidator]" maxlength="30" />
                            </template>
                        </CreateHalfVCol>
                        <!-- 👉 Password -->
                        <CreateHalfVCol :mdl="3" :mdr="9" v-if="props.id == 0">
                            <template #name>패스워드</template>
                            <template #input>
                                <VTextField v-model="props.item.user_pw" counter prepend-inner-icon="tabler-lock"
                                    :rules="[requiredValidator, passwordValidator]"
                                    :append-inner-icon="is_show ? 'tabler-eye' : 'tabler-eye-off'"
                                    :type="is_show ? 'text' : 'password'" placeholder="소문자,대문자,특수문자로 이루어진 8자 이상 문자열"
                                    persistent-placeholder @click:append-inner="is_show = !is_show" autocomplete />
                            </template>
                        </CreateHalfVCol>
                        <!-- 👉 대표자명 -->
                        <CreateHalfVCol :mdl="3" :mdr="9">
                            <template #name>대표자명</template>
                            <template #input>
                                <VTextField id="nickNameHorizontalIcons" v-model="props.item.nick_name"
                                    prepend-inner-icon="tabler-user" placeholder="사용자명으로 사용됩니다." persistent-placeholder />
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
                                <VTextField id="mobileHorizontalIcons" v-model="props.item.phone_num" type="number"
                                    prepend-inner-icon="tabler-device-mobile" placeholder="숫자만 입력해주세요."
                                    persistent-placeholder />
                            </template>
                        </CreateHalfVCol>
                        <!-- 👉 사업자 번호 -->
                        <CreateHalfVCol :mdl="3" :mdr="9">
                            <template #name>사업자번호</template>
                            <template #input>
                                <VTextField id="businessHorizontalIcons" v-model="props.item.business_num" type="text"
                                    prepend-inner-icon="ic-outline-business-center" placeholder="사업자번호 입력"
                                    persistent-placeholder
                                    :rules="[requiredValidator, businessNumValidator(props.item.business_num)]" />
                            </template>
                        </CreateHalfVCol>
                        <!-- 👉 주민등록 번호 -->
                        <CreateHalfVCol :mdl="3" :mdr="9">
                            <template #name>주민등록번호</template>
                            <template #input>
                                <VTextField id="residentFirstHorizontalIcons" v-model="props.item.resident_num" type="text"
                                    counter prepend-inner-icon="carbon-identification" placeholder="앞자리 입력"
                                    persistent-placeholder
                                    :rules="[requiredValidator, lengthValidatorV2(props.item.resident_num, 13)]"
                                    maxlength="13" />
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
                                <VTextField id="acctNumHorizontalIcons" type="number" v-model="props.item.acct_num"
                                    prepend-inner-icon="ri-bank-card-fill" placeholder="계좌번호 입력" persistent-placeholder />
                            </template>
                        </CreateHalfVCol>
                        <CreateHalfVCol :mdl="3" :mdr="9">
                            <template #name>예금주</template>
                            <template #input>
                                <VTextField id="acctNmHorizontalIcons" v-model="props.item.acct_nm"
                                    prepend-inner-icon="tabler-user" placeholder="예금주 입력" persistent-placeholder />
                            </template>
                        </CreateHalfVCol>
                        <CreateHalfVCol :mdl="3" :mdr="9">
                            <template #name>은행</template>
                            <template #input>
                                <VAutocomplete :menu-props="{ maxHeight: 400 }" v-model="bank" :items="banks"
                                    prepend-inner-icon="ph-buildings" label="은행 선택"
                                    :hint="`${bank.title}, 은행 코드: ${bank.code} `" item-title="title" item-value="code"
                                    persistent-hint return-object single-line />
                            </template>
                        </CreateHalfVCol>
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
                                <FileInput :file="props.item.passbook_file" :label="`통장사본 업로드`"
                                    :preview="props.item.passbook_img" @update:file="props.item.passbook_file = $event" />
                            </VRow>
                        </VCol>
                        <VCol cols="12">
                            <VRow no-gutters>
                                <FileInput :file="props.item.id_file" :label="`신분증 업로드`" :preview="props.item.id_img"
                                    @update:file="props.item.id_file = $event" />
                            </VRow>
                        </VCol>
                        <VCol cols="12">
                            <VRow no-gutters>
                                <FileInput :file="props.item.contract_file" :label="`계약서 업로드`"
                                    :preview="props.item.contract_img" @update:file="props.item.contract_file = $event" />
                            </VRow>
                        </VCol>
                        <VCol cols="12">
                            <VRow no-gutters>
                                <FileInput :file="props.item.bsin_lic_file" :label="`사업자 등록증 업로드`"
                                    :preview="props.item.bsin_lic_img" @update:file="props.item.bsin_lic_file = $event" />
                            </VRow>
                        </VCol>
                    </VRow>
                </VCardItem>
            </VCard>
        </VCol>
    </VRow>
</template>
  
