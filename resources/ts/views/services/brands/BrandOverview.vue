<script lang="ts" setup>
import { businessNumValidator, requiredValidator } from '@validators'
import type { Brand } from '@/views/types'
import FileInput from '@/layouts/utils/FileInput.vue'
import CreateHalfVCol from '@/layouts/utils/CreateHalfVCol.vue'
import { themeConfig } from '@themeConfig'
import { config } from '@layouts/config'
import { getUserLevel } from '@/plugins/axios';
import { dev_settle_types } from '@/views/services/brands/useStore'
import BaseQuestionTooltip from '@/layouts/tooltips/BaseQuestionTooltip.vue'
import { nullValidator } from '@validators'

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
                                <VTextField v-model="props.item.company_name" prepend-inner-icon="ph-buildings"
                                    placeholder="회사명을 입력해주세요" persistent-placeholder :rules="[requiredValidator]" />
                            </template>
                        </CreateHalfVCol>
                        <!-- 👉 대표자명 -->
                        <CreateHalfVCol :mdl="3" :mdr="9">
                            <template #name>대표자명</template>
                            <template #input>
                                <VTextField id="nickNameHorizontalIcons" v-model="props.item.ceo_name"
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
                                    prepend-inner-icon="tabler-device-mobile" placeholder="휴대폰번호 입력" persistent-placeholder
                                    :rules="[requiredValidator]" />
                            </template>
                        </CreateHalfVCol>
                        <!-- 👉 사업자등록번호 -->
                        <CreateHalfVCol :mdl="3" :mdr="9">
                            <template #name>사업자등록번호</template>
                            <template #input>
                                <VTextField id="businessHorizontalIcons" v-model="props.item.business_num" type="text"
                                    prepend-inner-icon="ic-outline-business-center" placeholder="123-12-12345"
                                    persistent-placeholder
                                    :rules="[requiredValidator, businessNumValidator(props.item.business_num)]" />
                            </template>
                        </CreateHalfVCol>
                    </VRow>
                </VCardItem>
                <VCardItem v-if="getUserLevel() == 50">
                    <VCardTitle>
                        <BaseQuestionTooltip location="top" text="지불정보" :content="item.pv_options.auth.levels.dev_name+'만 확인 가능한 정보입니다.'"></BaseQuestionTooltip>
                    </VCardTitle>
                    <VRow class="pt-5">
                            <CreateHalfVCol :mdl="6" :mdr="6">
                                <template #name><span>{{ item.pv_options.auth.levels.dev_name }} 사용여부</span></template>
                                <template #input>
                                    <BooleanRadio :radio="item.pv_options.auth.levels.dev_use" @update:radio="item.pv_options.auth.levels.dev_use = $event">
                                        <template #true>사용</template>
                                        <template #false>미사용</template>
                                    </BooleanRadio>
                                </template>
                            </CreateHalfVCol>
                        <CreateHalfVCol :mdl="6" :mdr="6">
                            <template #name><span>개발사</span></template>
                            <template #input>
                                <VTextField v-model="props.item.pv_options.auth.levels.dev_name"
                                    prepend-inner-icon="ph:share-network" placeholder="개발사 등급 명칭을 입력해주세요"
                                    persistent-placeholder :rules="[nullValidator]" />
                            </template>
                        </CreateHalfVCol>
                        <CreateHalfVCol :mdl="6" :mdr="6">
                            <template #name><span></span>입금일</template>
                            <template #input>
                                <VTextField prepend-inner-icon="tabler-calendar" v-model="props.item.deposit_day"
                                    type="number" :rules="[requiredValidator]" />
                            </template>
                        </CreateHalfVCol>
                        <CreateHalfVCol :mdl="6" :mdr="6">
                            <template #name><span></span>입금액</template>
                            <template #input>
                                <VTextField prepend-inner-icon="tabler-currency-won" v-model="props.item.deposit_amount"
                                    type="number" :rules="[requiredValidator]" />
                            </template>
                        </CreateHalfVCol>
                        <CreateHalfVCol :mdl="6" :mdr="6">
                            <template #name><span></span>부가 입금액</template>
                            <template #input>
                                <VTextField prepend-inner-icon="tabler-currency-won"
                                    v-model="props.item.extra_deposit_amount" type="number" :rules="[requiredValidator]" />
                            </template>
                        </CreateHalfVCol>
                        <CreateHalfVCol :mdl="6" :mdr="6">
                            <template #name><span></span>{{ props.item.pv_options.auth.levels.dev_name }} 수수료</template>
                            <template #input>
                                <VTextField v-model="props.item.dev_fee" type="number" :rules="[requiredValidator]"
                                    suffix="%" />
                            </template>
                        </CreateHalfVCol>
                        <CreateHalfVCol :mdl="6" :mdr="6">
                            <template #name><span></span>수수료 정산 타입</template>
                            <template #input>

                                <VSelect :menu-props="{ maxHeight: 400 }" v-model="props.item.dev_settle_type"
                                    :items="dev_settle_types" prepend-inner-icon="ph-buildings" label="수수료 정산 타입 선택"
                                    item-title="title" item-value="id" single-line :rules="[requiredValidator]" />
                            </template>
                        </CreateHalfVCol>
                        <VCol>
                            <VTextarea v-model="props.item.note" counter label="메모사항"
                                prepend-inner-icon="twemoji-spiral-notepad" />
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
                                    :preview="props.item.passbook_img ?? '/icons/img-preview.svg'"
                                    @update:file="props.item.passbook_file = $event" />
                            </VRow>
                        </VCol>
                        <VCol cols="12">
                            <VRow no-gutters>
                                <FileInput :label="`신분증 업로드`" :preview="props.item.id_img ?? '/icons/img-preview.svg'"
                                    @update:file="props.item.id_file = $event" />
                            </VRow>
                        </VCol>
                        <VCol cols="12">
                            <VRow no-gutters>
                                <FileInput :label="`계약서 업로드`" :preview="props.item.contract_img ?? '/icons/img-preview.svg'"
                                    @update:file="props.item.contract_file = $event" />
                            </VRow>
                        </VCol>
                        <VCol cols="12">
                            <VRow no-gutters>
                                <FileInput :label="`사업자 등록증 업로드`"
                                    :preview="props.item.bsin_lic_img ?? '/icons/img-preview.svg'"
                                    @update:file="props.item.bsin_lic_file = $event" />
                            </VRow>
                        </VCol>
                    </VRow>
                </VCardItem>
            </VCard>
        </VCol>
        <!-- 👉 submit -->
    </VRow>
</template>
