<script setup lang="ts">
import BaseQuestionTooltip from '@/layouts/tooltips/BaseQuestionTooltip.vue';
import CreateHalfVCol from '@/layouts/utils/CreateHalfVCol.vue';
import type { AuthOption, FreeOption, PaidOption } from '@/views/types';
import { getUserLevel } from '@axios';
interface Props {
    item: {
        free: FreeOption,
        paid: PaidOption,
        auth: AuthOption,
    },
}
const props = defineProps<Props>()
</script>

<template>
    <VRow class="match-height" v-if="getUserLevel() === 50">
        <VCol cols="12" md="6">
            <VCard>
                <VCardItem>
                    <VCardTitle>
                        <BaseQuestionTooltip location="top" text="브랜드 옵션(유료)"
                            :content="`개발사만 확인 가능한 정보입니다.`"/>
                    </VCardTitle>
                    <VCol>
                        <VRow>
                            <CreateHalfVCol :mdl="6" :mdr="6">
                                <template #name>
                                    결제모듈 사용여부
                                </template>
                                <template #input>
                                    <VSwitch hide-details v-model="props.item.paid.use_pay_module" color="primary" />
                                </template> 
                            </CreateHalfVCol>
                        </VRow>
                    </VCol>
                </VCardItem>
            </VCard>
        </VCol>
  </VRow>
</template>
