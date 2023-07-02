<script lang="ts" setup>
import { salesLevels, settleCycles, settleDays, settleTaxTypes } from '@/views/salesforces/useStore'
import BooleanRadio from '@/layouts/utils/BooleanRadio.vue'
import CreateHalfVCol from '@/layouts/utils/CreateHalfVCol.vue'
import BaseQuestionTooltip from '@/layouts/tooltips/BaseQuestionTooltip.vue'
import type { Salesforce } from '@/views/types'
import { requiredValidator, nullValidator } from '@validators'

interface Props {
    item: Salesforce,
}
const props = defineProps<Props>()
const all_sales = salesLevels()
const all_cycles = settleCycles()
const all_days = settleDays()
const tax_types = settleTaxTypes()

</script>
<template>
    <VRow class="match-height">
        <!-- 👉 개인정보 -->
        <VCol cols="12" md="6">
            <VCard>
                <VCardItem>
                    <VCardTitle>영업점정보</VCardTitle>
                    <VRow class="pt-5">
                        <CreateHalfVCol :mdl="3" :mdr="9">
                            <template #name>영업점 상호</template>
                            <template #input>
                                <VTextField v-model="props.item.sales_name" prepend-inner-icon="tabler-building-store"
                                    placeholder="상호를 입력해주세요" persistent-placeholder :rules="[requiredValidator]" />
                            </template>
                        </CreateHalfVCol>
                        <CreateHalfVCol :mdl="3" :mdr="9">
                            <template #name>정산 세율</template>
                            <template #input>
                                <VRadioGroup v-model="props.item.settle_tax_type" inline :rules="[nullValidator]">
                                    <VRadio v-for="(tax_type, key, index) in tax_types" :value="tax_type.id" :key="index">
                                        <template #label>
                                            <span>
                                                {{ tax_type.title }}
                                            </span>
                                        </template>
                                    </VRadio>
                                </VRadioGroup>
                            </template>
                        </CreateHalfVCol>
                        <CreateHalfVCol :mdl="3" :mdr="9">
                            <template #name>
                                정산 주기
                            </template>
                            <template #input>
                                <VSelect :menu-props="{ maxHeight: 400 }" v-model="props.item.settle_cycle"
                                    :items="all_cycles" prepend-inner-icon="icon-park-outline:cycle" label="정산 주기 선택"
                                    item-title="title" item-value="id" persistent-hint single-line
                                    :rules="[nullValidator]" />
                            </template>
                        </CreateHalfVCol>
                        <CreateHalfVCol :mdl="3" :mdr="9">
                            <template #name>
                                정산 요일
                            </template>
                            <template #input>
                                <VSelect :menu-props="{ maxHeight: 400 }" v-model="props.item.settle_day" :items="all_days"
                                    prepend-inner-icon="icon-park-outline:cycle" label="정산 요일 선택" item-title="title"
                                    item-value="id" persistent-hint single-line />
                            </template>
                        </CreateHalfVCol>
                        <CreateHalfVCol :mdl="3" :mdr="9">
                            <template #name>
                                <BaseQuestionTooltip :location="'top'" :text="'등급'" :content="'영업자 등급은 수정할 수 없습니다.'">
                                </BaseQuestionTooltip>
                            </template>
                            <template #input>
                                <VSelect :menu-props="{ maxHeight: 400 }" v-model="props.item.level" :items="all_sales"
                                    prepend-inner-icon="ph:share-network" label="영업자 등급 선택" item-title="title" item-value="id"
                                    persistent-hint single-line :rules="[nullValidator]" :readonly="props.item.id != 0" />
                            </template>
                        </CreateHalfVCol>
                        <VCol>
                            <VTextarea v-model="props.item.note" counter label="메모사항"
                                prepend-inner-icon="twemoji-spiral-notepad" maxlength="100"/>
                        </VCol>
                    </VRow>
                </VCardItem>
            </VCard>
        </VCol>
        <VCol cols="12" md="6">
            <VCard>
                <VCardItem>
                    <VCardTitle>옵션</VCardTitle>
                    <VCol cols="12">
                        <VRow>
                            <CreateHalfVCol :mdl="3" :mdr="9">
                                <template #name>화면 타입</template>
                                <template #input>
                                    <BooleanRadio :radio="Boolean(props.item.view_type)"
                                        @update:radio="props.item.view_type = $event">
                                        <template #true>상세보기</template>
                                        <template #false>간편보기</template>
                                    </BooleanRadio>
                                </template>
                            </CreateHalfVCol>
                        </VRow>
                    </VCol>
                </VCardItem>
            </VCard>
        </VCol>
        <!-- 👉 submit -->
    </VRow>
</template>
