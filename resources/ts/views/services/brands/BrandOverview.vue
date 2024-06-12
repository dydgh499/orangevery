<script lang="ts" setup>
import BaseQuestionTooltip from '@/layouts/tooltips/BaseQuestionTooltip.vue'
import FileInput from '@/layouts/utils/FileInput.vue'
import { getUserLevel } from '@/plugins/axios'
import BeforeBrandInfoCard from '@/views/services/brands/before-brand-infos/BeforeBrandInfoCard.vue'
import DifferentSettlementInfoCard from '@/views/services/brands/different-settlement-infos/DifferentSettlementInfoCard.vue'
import OperatorIpCard from '@/views/services/brands/operator-ips/OperatorIpCard.vue'
import { dev_settle_types } from '@/views/services/brands/useStore'
import type { Brand } from '@/views/types'
import corp from '@corp'
import { config } from '@layouts/config'
import { themeConfig } from '@themeConfig'
import { requiredValidatorV2 } from '@validators'

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
    <VRow>
        <!-- 👉 운영정보 -->
        <VCol cols="12" md="6">
            <VCard>
                <VCardItem>
                    <VCardTitle>운영정보</VCardTitle>            
                    <VRow class="pt-3">
                        <VCol :md="6" :cols="12">
                            <VRow no-gutters>
                                <VCol>
                                    <label>도메인</label>
                                </VCol>
                                <VCol md="8">
                            <VTextField v-model="props.item.dns" prepend-inner-icon="tabler-world-www"
                                placeholder="도메인을 입력해주세요" persistent-placeholder :rules="[requiredValidatorV2(props.item.dns, '도메인')]" />
                                </VCol>
                            </VRow>
                        </VCol>
                        <VCol :md=6 v-if="getUserLevel() == 50">
                            <VRow no-gutters>
                                <VCol>
                                    <label>차액정산 사용여부</label>
                                </VCol>
                                <VCol md="8">
                                    <VSwitch hide-details :false-value=0 :true-value=1 v-model="props.item.use_different_settlement" color="primary" />
                                </VCol>
                            </VRow>
                        </VCol>
                    </VRow>
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
                        <VCol :md="6" :cols="12">
                            <VRow no-gutters>
                                <VCol>
                                    <label>운영사명</label>
                                </VCol>
                                <VCol md="8">
                                    <VTextField v-model="props.item.name" prepend-inner-icon="twemoji-desktop-computer"
                                        placeholder="운영사명을 입력해주세요" persistent-placeholder :rules="[requiredValidatorV2(props.item.name, '운영사명')]" />
                                </VCol>
                            </VRow>
                        </VCol>
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
                    </VRow>
                    <VRow class="pt-3">
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
                        <VCol :md=6>
                            <VRow no-gutters>
                                <VCol>
                                    <label>팩스번호</label>
                                </VCol>
                                <VCol md="8">
                                    <VTextField v-model="props.item.fax_num"
                                        prepend-inner-icon="streamline-emojis:fax-machine" placeholder="팩스번호 입력" persistent-placeholder
                                        maxlength="200" :rules="[requiredValidatorV2(props.item.fax_num, '팩스번호')]" />
                                </VCol>
                            </VRow>
                        </VCol>
                    </VRow>
                </VCardItem>
            </VCard>
            <br>
            <VCard v-if="getUserLevel() == 50">
                <VCardItem>
                    <VCardTitle>
                        <BaseQuestionTooltip location="top" text="개발사 정보"
                            :content="props.item.pv_options.auth.levels.dev_name + '만 확인 가능한 정보입니다.'"></BaseQuestionTooltip>
                    </VCardTitle>
                    <VRow class="pt-3">
                        <VCol :md="6" :cols="12">
                            <VRow no-gutters>
                                <VCol>
                                    <label>{{ props.item.pv_options.auth.levels.dev_name }} 사용여부</label>
                                </VCol>
                                <VCol md="8">
                                    <VSwitch hide-details :false-value=0 :true-value=1 v-model="props.item.pv_options.auth.levels.dev_use"
                                        color="primary" />
                                </VCol>
                            </VRow>
                        </VCol>
                        <VCol :md=6>
                            <VRow no-gutters>
                                <VCol>
                                    <label>입금일</label>
                                </VCol>
                                <VCol md="8">
                                    <VTextField prepend-inner-icon="tabler-calendar" v-model="props.item.deposit_day"
                                        type="number" :rules="[requiredValidatorV2(props.item.deposit_day, '입금일')]" />
                                </VCol>
                            </VRow>
                        </VCol>
                    </VRow>
                    <VRow class="pt-3">
                        <VCol :md="6" :cols="12">
                            <VRow no-gutters>
                                <VCol>
                                    <label>개발사 명칭설정</label>
                                </VCol>
                                <VCol md="8">
                                    <VTextField v-model="props.item.pv_options.auth.levels.dev_name"
                                        prepend-inner-icon="ph:share-network" placeholder="개발사 등급 명칭을 입력해주세요"
                                        persistent-placeholder />
                                </VCol>
                            </VRow>
                        </VCol>
                        <VCol :md=6>
                            <VRow no-gutters>
                                <VCol>
                                    <label>입금액</label>
                                </VCol>
                                <VCol md="8">
                                    <VTextField prepend-inner-icon="tabler-currency-won" v-model="props.item.deposit_amount"
                                        type="number" :rules="[requiredValidatorV2(props.item.deposit_amount, '입금액')]" />
                                </VCol>
                            </VRow>
                        </VCol>
                    </VRow>
                    <VRow class="pt-3">
                        <VCol :md="6" :cols="12">
                            <VRow no-gutters>
                                <VCol>
                                    <label>{{ props.item.pv_options.auth.levels.dev_name }} 수수료</label>
                                </VCol>
                                <VCol md="8">
                                    <VTextField v-model="props.item.dev_fee" type="number"
                                        suffix="%" />
                                </VCol>
                            </VRow>
                        </VCol>
                        <VCol :md=6>
                            <VRow no-gutters>
                                <VCol>
                                    <label>부가 입금액</label>
                                </VCol>
                                <VCol md="8">
                                    <VTextField prepend-inner-icon="tabler-currency-won"
                                        v-model="props.item.extra_deposit_amount" type="number" />
                                </VCol>
                            </VRow>
                        </VCol>
                    </VRow>
                    <VRow class="pt-3">
                        <VCol :md="6" :cols="12">
                            <VRow no-gutters>
                                <VCol>
                                    <label>수수료 정산 타입</label>
                                </VCol>
                                <VCol md="8">
                                    <VSelect :menu-props="{ maxHeight: 400 }" v-model="props.item.dev_settle_type"
                                        :items="dev_settle_types" prepend-inner-icon="ph-buildings" label="수수료 정산 타입 선택"
                                        item-title="title" item-value="id" single-line :rules="[requiredValidatorV2(props.item.dev_settle_type, '수수료 정산 타입')]" />

                                </VCol>
                            </VRow>
                        </VCol>
                        <VCol :md=6>
                            <VRow no-gutters>
                                <VCol>
                                    <label>메모사항</label>
                                </VCol>
                                <VCol md="8">
                                    <VTextarea v-model="props.item.note" counter label="메모사항"
                                        prepend-inner-icon="twemoji-spiral-notepad" maxlength="250" auto-grow/>
                                </VCol>
                            </VRow>
                        </VCol>
                    </VRow>
                </VCardItem>
            </VCard>
            <br>
            <VCard v-if="props.item.use_different_settlement">
                <VCardItem>
                    <VCol cols="12">
                        <VRow>
                            <DifferentSettlementInfoCard :item="props.item" />
                        </VRow>
                    </VCol>
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
                </VCardItem>
            </VCard>
            <br>
            <VCard v-if="getUserLevel() === 50 && corp.id === 1">
                <VCardItem>
                    <VCol cols="12">
                        <VRow>
                            <OperatorIpCard :item="props.item" />
                        </VRow>
                    </VCol>
                </VCardItem>
            </VCard>
            <br>
            <VCard v-if="props.item.pv_options.paid.use_before_brand_info">
                <VCardItem>
                    <VCol cols="12">
                        <VRow>
                            <BeforeBrandInfoCard :item="props.item" />
                        </VRow>
                    </VCol>
                </VCardItem>
            </VCard>
        </VCol>
        <!-- 👉 submit -->
    </VRow>
</template>
