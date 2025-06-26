<script setup lang="ts">

import BaseQuestionTooltip from '@/layouts/tooltips/BaseQuestionTooltip.vue';
import CreateHalfVCol from '@/layouts/utils/CreateHalfVCol.vue';
import { fee_input_modes } from '@/views/services/brands/useStore';
import type { AuthOption, FreeOption, PaidOption } from '@/views/types';
import { getUserLevel } from '@axios';
import corp from '@corp';
import { requiredValidatorV2 } from '@validators';
interface Props {
    item: {
        free: FreeOption,
        paid: PaidOption,
        auth: AuthOption,
    },
}
const props = defineProps<Props>()
const brand_modes = [
    {id:0, title: '대표가맹점(운영사)'},
    {id:1, title: '개인간거래'},
    {id:2, title: '대표가맹점(영업점)'},
]
</script>
<template>
    <VRow class="match-height" v-if="getUserLevel() === 50">
        <VCol cols="12" md="6">
            <VCard>
                <VCardItem>
                    <VCardTitle>
                        <BaseQuestionTooltip location="top" text="브랜드 옵션(유료)"
                            :content="`${corp.pv_options.auth.levels.dev_name}만 확인 가능한 정보입니다.`"/>
                    </VCardTitle>
                    <VCol>
                        <VRow>
                            <CreateHalfVCol :mdl="6" :mdr="6">
                                <template #name>
                                    <VSwitch hide-details v-model="props.item.paid.use_bill_key" color="primary" label="빌링결제"/>
                                </template>
                            </CreateHalfVCol>
                        </VRow>
                    </VCol>
                </VCardItem>
            </VCard>
        </VCol>
  </VRow>
</template>
