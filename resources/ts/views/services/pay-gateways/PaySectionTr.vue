<script setup lang="ts">
import { useRequestStore } from '@/views/request'
import type { PaySection } from '@/views/types'
import { HistoryTargetNames } from '@core/enums'
import { requiredValidatorV2 } from '@validators'
import { VForm } from 'vuetify/components'

interface Props {
    item: PaySection,
    index: number,
}
const vForm = ref<VForm>()
const props = defineProps<Props>()
const activityHistoryTargetDialog = <any>(inject('activityHistoryTargetDialog'))

const { update, remove } = useRequestStore()
</script>
<template>
    <tr>
        <td style="width: 10%;">{{ index + 1 }}</td>
        <td style="width: 30%;">
            <VForm ref="vForm">
                <VCol cols="12">
                    <VRow no-gutters>
                        <VTextField v-model="props.item.name" prepend-inner-icon="mdi-vector-intersection"
                            placeholder="구간명 입력" persistent-placeholder :rules="[requiredValidatorV2(props.item.name, '구간명')]"
                            style="display: inline-block; min-width: 10em;" />
                    </VRow>
                </VCol>
            </VForm>
        </td>
        <td style="width: 20%;">
            <VForm ref="vForm">
                <VCol cols="12">
                    <VRow no-gutters>
                        <VTextField v-model="props.item.trx_fee" prepend-inner-icon="tabler-percentage"
                            placeholder="수수료율 입력" persistent-placeholder :rules="[requiredValidatorV2(props.item.trx_fee, '수수료율')]"
                            style="display: inline-block; min-width: 10em;" />
                    </VRow>
                </VCol>
            </VForm>
        </td>
        <td class="text-center" style="width: 40%;">
            <VCol style="text-align: end;">
                <VBtn v-if="props.item.id"
                    style="margin-left: auto;"
                    color="secondary" 
                    variant="text"
                    @click="activityHistoryTargetDialog.show(props.item.id, HistoryTargetNames['services/pay-sections'])">
                    이력
                    <VIcon end size="20" icon="tabler:history" />
                </VBtn>                            
                <VBtn 
                    style="margin-left: 1em;"
                    variant="text"
                    @click="update('/services/pay-sections', props.item, vForm, false)">
                    {{ props.item.id == 0 ? "추가" : "수정" }}
                    <VIcon end size="20" icon="tabler-pencil" />
                </VBtn>
                <VBtn v-if="props.item.id"
                    style="margin-left: 1em;"
                    variant="text"
                    color="error"
                    @click="remove('/services/pay-sections', props.item, false)">
                    삭제
                    <VIcon end size="20" icon="tabler-trash" />
                </VBtn>
                <VBtn v-else
                    style="margin-left: 1em;"
                    color="warning"
                    variant="text"
                    @click="props.item.id = -1">
                    입력란 제거
                    <VIcon end size="20" icon="tabler-trash" />
                </VBtn>
            </VCol>
        </td>
    </tr>
</template>

