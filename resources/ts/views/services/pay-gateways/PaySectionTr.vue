<script setup lang="ts">
import { VForm } from 'vuetify/components'
import type { PaySection } from '@/views/types'
import { requiredValidator } from '@validators'
import { useRequestStore } from '@/views/request'

interface Props {
    item: PaySection,
    index: number,
}
const props = defineProps<Props>()
const vForm = ref<VForm>()

const { update, remove } = useRequestStore()
</script>
<template>
    <tr>
        <td style="width: 15%;">{{ index + 1 }}</td>
        <td style="width: 35%;">
            <VForm ref="vForm">
                <VCol cols="12">
                    <VRow no-gutters>
                        <VTextField v-model="props.item.name" prepend-inner-icon="mdi-vector-intersection"
                            placeholder="구간명 입력" persistent-placeholder :rules="[requiredValidator]"
                            style="display: inline-block;" />
                    </VRow>
                </VCol>
            </VForm>
        </td>
        <td style="width: 35%;">
            <VForm ref="vForm">
                <VCol cols="12">
                    <VRow no-gutters>
                        <VTextField v-model="props.item.trx_fee" prepend-inner-icon="tabler-percentage"
                            placeholder="수수료율 입력" persistent-placeholder :rules="[requiredValidator]"
                            style="display: inline-block;" />
                    </VRow>
                </VCol>
            </VForm>
        </td>
        <td class="text-center" style="width: 25%;">
            <VCol class="d-flex gap-4">
                <VBtn type="button" color="default" variant="text" @click="update('/services/pay-sections', props.item.id, props.item, vForm, false)">
                    {{ props.item.id == 0 ? "추가" : "수정" }}
                    <VIcon end icon="tabler-pencil" />
                </VBtn>
                <VBtn type="button" color="default" variant="text" v-if="props.item.id" @click="remove('/services/pay-sections', props.item.id, false)">
                    삭제
                    <VIcon end icon="tabler-trash" />
                </VBtn>
            </VCol>
        </td>
    </tr>
</template>

