<script lang="ts" setup>
import { axios } from '@axios';
import { businessNumValidator, nullValidator, requiredValidator } from '@validators';
import type { Brand } from '@/views/types'
import FileInput from '@/layouts/utils/FileInput.vue';
import CreateHalfVCol from '@/layouts/utils/CreateHalfVCol.vue';
import { themeConfig } from '@themeConfig'
import { config } from '@layouts/config'

interface Props {
    item: Brand,
}
const props = defineProps<Props>()

watchEffect(() => {
    config.app.title = props.item.name
    themeConfig.app.title = props.item.name
})
</script>
<template>
    <VRow class="match-height">
        <!-- 👉 운영정보 -->
        <VCol cols="12" md="6">
            <VCard>
                <VCardItem>
                    <VCardTitle>운영정보</VCardTitle>
                    <VRow class="pt-5">
                        <CreateHalfVCol :mdl="3" :mdr="9">
                            <template #name>도메인</template>
                            <template #input>
                                <VTextField v-model="props.item.dns" prepend-inner-icon="tabler-world-www"
                                    placeholder="도메인을 입력해주세요" persistent-placeholder :rules="[requiredValidator]" />
                            </template>
                        </CreateHalfVCol>
                        <CreateHalfVCol :mdl="3" :mdr="9">
                            <template #name>운영사명</template>
                            <template #input>
                                <VTextField v-model="props.item.name" prepend-inner-icon="twemoji-desktop-computer"
                                    placeholder="운영사명을 입력해주세요" persistent-placeholder :rules="[requiredValidator]" />
                            </template>
                        </CreateHalfVCol>
                        <CreateHalfVCol :mdl="3" :mdr="9">
                            <template #name>회사명</template>
                            <template #input>
                                <VTextField v-model="props.item.company_nm" prepend-inner-icon="ph-buildings"
                                    placeholder="회사명을 입력해주세요" persistent-placeholder :rules="[requiredValidator]" />
                            </template>
                        </CreateHalfVCol>
                        <!-- 👉 대표자명 -->
                        <CreateHalfVCol :mdl="3" :mdr="9">
                            <template #name>대표자명</template>
                            <template #input>
                                <VTextField id="nickNameHorizontalIcons" v-model="props.item.ceo_nm"
                                    prepend-inner-icon="tabler-user" placeholder="대표자명을 입력해주세요." persistent-placeholder
                                    :rules="[requiredValidator]" />
                            </template>
                        </CreateHalfVCol>
                        <!-- 👉 Address -->
                        <CreateHalfVCol :mdl="3" :mdr="9">
                            <template #name>주소</template>
                            <template #input>
                                <VTextField id="addressHorizontalIcons" v-model="props.item.addr"
                                    prepend-inner-icon="tabler-map-pin" placeholder="주소 입력" persistent-placeholder
                                    maxlength="200" :rules="[requiredValidator]" />
                            </template>
                        </CreateHalfVCol>
                        <!-- 👉 Mobile -->
                        <CreateHalfVCol :mdl="3" :mdr="9">
                            <template #name>휴대폰번호</template>
                            <template #input>
                                <VTextField id="mobileHorizontalIcons" v-model="props.item.phone_num" type="number"
                                    prepend-inner-icon="tabler-device-mobile" placeholder="숫자만 입력해주세요."
                                    persistent-placeholder :rules="[requiredValidator]" />
                            </template>
                        </CreateHalfVCol>
                        <!-- 👉 사업자 번호 -->
                        <CreateHalfVCol :mdl="3" :mdr="9">
                            <template #name>사업자번호</template>
                            <template #input>
                                <VTextField id="businessHorizontalIcons" v-model="props.item.business_num" type="text"
                                    prepend-inner-icon="ic-outline-business-center" placeholder="123-12-12345"
                                    persistent-placeholder
                                    :rules="[requiredValidator, businessNumValidator(props.item.business_num)]" />
                            </template>
                        </CreateHalfVCol>
                        <VCol>
                            <VTextarea v-model="props.item.note" counter label="메모사항"
                                prepend-inner-icon="twemoji-spiral-notepad" />
                        </VCol>
                        <VDivider />
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
        <!-- 👉 submit -->
</VRow></template>
