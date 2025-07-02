<script lang="ts" setup>
import CreateHalfVColV2 from '@/layouts/utils/CreateHalfVColV2.vue';
import BaseQuestionTooltip from '@/layouts/tooltips/BaseQuestionTooltip.vue'
import FileInput from '@/layouts/utils/FileInput.vue'
import { getUserLevel } from '@/plugins/axios'
import type { Brand } from '@/views/types'
import { requiredValidatorV2 } from '@validators'

interface Props {
    item: Brand,
}
const props = defineProps<Props>()

</script>
<template>
    <VRow class="match-height">
        <!-- 👉 운영정보 -->
        <VCol cols="12" md="6">
            <VCard>
                <VCardItem>
                    <VCardTitle>운영정보</VCardTitle>
                    <VRow class="pt-3" >
                        <VCol :md="12" :cols="12">
                            <VRow no-gutters>
                                <VCol>
                                    <label>주소</label>
                                </VCol>
                                <VCol md="10">
                                    <VTextField id="addressHorizontalIcons" v-model="props.item.addr"
                                        prepend-inner-icon="tabler-map-pin" placeholder="주소 입력" persistent-placeholder
                                        maxlength="200" :rules="[requiredValidatorV2(props.item.addr, '주소')]" />
                                </VCol>
                            </VRow>
                        </VCol>
                    </VRow>
                    <VRow class="pt-3">
                        <VCol :md=6>
                            <VRow no-gutters>
                                <VCol>
                                    <label>회사명</label>
                                </VCol>
                                <VCol md="8">
                                    <VTextField v-model="props.item.company_name" prepend-inner-icon="ph-buildings"
                                        placeholder="회사명을 입력해주세요" persistent-placeholder :rules="[requiredValidatorV2(props.item.company_name, '회사명')]" />
                                </VCol>
                            </VRow>
                        </VCol>
                        <VCol :md="6" :cols="12">
                            <VRow no-gutters>
                                <VCol>
                                    <label>대표자명</label>
                                </VCol>
                                <VCol md="8">
                                    <VTextField v-model="props.item.ceo_name"
                                        prepend-inner-icon="tabler-user" placeholder="대표자명을 입력해주세요." persistent-placeholder
                                        :rules="[requiredValidatorV2(props.item.ceo_name, '대표자명')]" />
                                </VCol>
                            </VRow>
                        </VCol>
                    </VRow>
                    <VRow class="pt-3">
                        <VCol :md=6>
                            <VRow no-gutters>
                                <VCol>
                                    <label>사업자등록번호</label>
                                </VCol>
                                <VCol md="8">
                                    <VTextField id="businessHorizontalIcons" v-model="props.item.business_num" type="text"
                                        prepend-inner-icon="ic-outline-business-center" placeholder="123-12-12345"
                                        persistent-placeholder
                                        :rules="[requiredValidatorV2(props.item.business_num, '사업자등록번호')]" />
                                </VCol>
                            </VRow>
                        </VCol>
                        <VCol :md=6>
                            <VRow no-gutters>
                                <VCol>
                                    <label>팩스번호</label>
                                </VCol>
                                <VCol md="8">
                                    <VTextField v-model="props.item.fax_num"
                                        prepend-inner-icon="streamline-emojis:fax-machine" placeholder="팩스번호 입력" persistent-placeholder
                                        maxlength="15" :rules="[requiredValidatorV2(props.item.fax_num, '팩스번호')]" />
                                </VCol>
                            </VRow>
                        </VCol>
                    </VRow>
                    <VRow class="pt-3">
                        <VCol :md="6" :cols="12">
                            <VRow no-gutters>
                                <VCol>
                                    <label>휴대폰번호</label>
                                </VCol>
                                <VCol md="8">
                                    <VTextField id="mobileHorizontalIcons" v-model="props.item.phone_num" type="number"
                                        prepend-inner-icon="tabler-device-mobile" placeholder="휴대폰번호 입력" persistent-placeholder
                                        :rules="[requiredValidatorV2(props.item.phone_num, '휴대폰번호')]" />
                                </VCol>
                            </VRow>
                        </VCol>
                    </VRow>
                    <VRow>
                        <VCol :md="12" :cols="12" v-if="getUserLevel() === 50">
                            <VRow no-gutters>
                                <VCol md="12">
                                    <VTextarea v-model="props.item.note" counter label="메모사항"
                                        variant="filled"
                                        prepend-inner-icon="twemoji-spiral-notepad" maxlength="250" auto-grow/>
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
                        <VCol cols="12" md="6">
                            <VRow no-gutters>
                                <FileInput :label="`통장사본 업로드`"
                                    :preview="props.item.passbook_img ? props.item.passbook_img : '/utils/icons/img-preview.svg'"
                                    @update:file="props.item.passbook_file = $event" 
                                    @update:path="props.item.passbook_img = $event" />
                            </VRow>
                        </VCol>
                        <VCol cols="12" md="6">
                            <VRow no-gutters>
                                <FileInput :label="`신분증 업로드`" :preview="props.item.id_img ? props.item.id_img : '/utils/icons/img-preview.svg'"
                                    @update:file="props.item.id_file = $event" 
                                    @update:path="props.item.id_img = $event" />
                            </VRow>
                        </VCol>
                        <VCol cols="12" md="6">
                            <VRow no-gutters>
                                <FileInput :label="`계약서 업로드`"
                                    :preview="props.item.contract_img ? props.item.contract_img : '/utils/icons/img-preview.svg'"
                                    @update:file="props.item.contract_file = $event" 
                                    @update:path="props.item.contract_img = $event" />
                            </VRow>
                        </VCol>
                        <VCol cols="12" md="6">
                            <VRow no-gutters>
                                <FileInput :label="`사업자 등록증 업로드`"
                                    :preview="props.item.bsin_lic_img ?? '/utils/icons/img-preview.svg'"
                                    @update:file="props.item.bsin_lic_file = $event" 
                                    @update:path="props.item.bsin_lic_img = $event" />
                            </VRow>
                        </VCol>
                    </VRow>
                    <br>
                    <VCardTitle class="mt-5 mb-5">옵션</VCardTitle>
                        <CreateHalfVColV2 :mdl="8" :mdr="4">
                            <template #l_name>
                                <BaseQuestionTooltip location="top" text="계좌번호 중복검사 사용" :content="`가상계좌 대량출금 탭의 출금예약시 계좌번호의 중복 입력을 검사합니다.`"/>
                                </template>
                            <template #l_input>
                                <VSwitch hide-details v-model="props.item.ov_options.free.use_account_number_duplicate" color="primary" />
                            </template>
                        </CreateHalfVColV2>
                </VCardItem>
            </VCard>
        </VCol>
        <!-- 👉 submit -->
    </VRow>
</template>
