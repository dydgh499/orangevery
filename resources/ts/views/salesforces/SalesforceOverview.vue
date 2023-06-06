<script lang="ts" setup>
import type { Salesforce } from '@/views/types'
import CreateHalfVCol from '@/layouts/utils/CreateHalfVCol.vue';
import { requiredValidator, nullValidator } from '@validators';
import { salesLevels } from '@/views/salesforces/useStore'

interface Props {
    item: Salesforce,
}
const props = defineProps<Props>()
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
                                <VRadioGroup v-model="props.item.tax_type" inline :rules="[nullValidator]">
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
                            <template #name>등급</template>
                            <template #input>
                                <VSelect :menu-props="{ maxHeight: 400 }" v-model="props.item.level"
                                        :items="salesLevels" prepend-inner-icon="tabler-man" label="정산자 선택" item-title="title"
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
