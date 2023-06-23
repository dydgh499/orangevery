<script lang="ts" setup>
import type { Salesforce } from '@/views/types'
import CreateHalfVCol from '@/layouts/utils/CreateHalfVCol.vue';
import { requiredValidator, nullValidator } from '@validators';
import { salesLevels, settleCycles, settleDays } from '@/views/salesforces/useStore'

interface Props {
    item: Salesforce,
}
const props = defineProps<Props>()
const all_sales = salesLevels()
const all_cycles = settleCycles()
const all_days = settleDays()

</script>
<template>
    <VRow class="match-height">
        <!-- 👉 개인정보 -->
        <VCol cols="12" md="12">
            <VCard>
                <VCardItem>
                    <VCardTitle>영업점정보</VCardTitle>
                    <VRow class="pt-5">                        
                        <CreateHalfVCol :mdl="3" :mdr="9">
                            <template #name>업종</template>
                            <template #input>
                                <VTextField v-model="props.item.sector"
                                    prepend-inner-icon="tabler-building-store" placeholder="업종을 입력해주세요"
                                    persistent-placeholder :rules="[requiredValidator]" />
                            </template>
                        </CreateHalfVCol>
                        <CreateHalfVCol :mdl="3" :mdr="9">
                            <template #name>정산 세율</template>
                            <template #input>
                                <VRadioGroup v-model="props.item.settle_tax_type" inline :rules="[nullValidator]">
                                        <VRadio :value="0">
                                            <template #label>
                                                <span>
                                                    세율 없음
                                                </span>
                                            </template>
                                        </VRadio>

                                        <VRadio :value="1">
                                            <template #label>
                                                <span>
                                                    3.3%
                                                </span>
                                            </template>
                                        </VRadio>
                                        <VRadio :value="2">
                                            <template #label>
                                                <span>
                                                    10%
                                                </span>
                                            </template>
                                        </VRadio>
                                        <VRadio :value="3">
                                            <template #label>
                                                <span>
                                                    10+3.3%
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
                                        :items="all_cycles" prepend-inner-icon="icon-park-outline:cycle" label="정산 요일 선택" item-title="title"
                                        item-value="id" persistent-hint single-line :rules="[nullValidator]"/>
                            </template>
                        </CreateHalfVCol>
                        <CreateHalfVCol :mdl="3" :mdr="9">
                            <template #name>
                                    정산 요일
                            </template>
                            <template #input>
                                <VSelect :menu-props="{ maxHeight: 400 }" v-model="props.item.settle_days"
                                        :items="all_days" prepend-inner-icon="icon-park-outline:cycle" label="정산 주기 선택" item-title="title"
                                        item-value="id" persistent-hint single-line :rules="[nullValidator]"/>
                            </template>
                        </CreateHalfVCol>
                        <CreateHalfVCol :mdl="3" :mdr="9">
                            <template #name>
                                <div class="d-inline-flex align-center gap-2 justify-content-evenly">
                                    <span>
                                        등급
                                    </span>
                                    <VTooltip open-on-click :open-on-hover="false" location="top" transition="scale-transition">
                                        <template #activator="{ props }">
                                            <VIcon v-bind="props" size="20" icon="ic:outline-help" color="primary"
                                                style="margin-bottom: 0.2em;" />
                                        </template>
                                        <span>
                                            영업자 등급은 수정할 수 없습니다.
                                        </span>
                                    </VTooltip>
                                </div>
                            </template>
                            <template #input>
                                <VSelect :menu-props="{ maxHeight: 400 }" v-model="props.item.level"
                                        :items="all_sales" prepend-inner-icon="tabler-man" label="영업자 등급 선택" item-title="title"
                                        item-value="id" persistent-hint single-line :rules="[nullValidator]" 
                                        :readonly="props.item.id != 0"/>
                            </template>
                        </CreateHalfVCol>
                    </VRow>                       
                </VCardItem>
            </VCard>
        </VCol>
        <!-- 👉 submit -->
    </VRow>
</template>
