<script setup lang="ts">
import { useRequestStore } from '@/views/request'
import type { Classification } from '@/views/types'
import { requiredValidatorV2 } from '@validators'
import { VForm } from 'vuetify/components'

interface Props {
    item: Classification,
    index: number,
    base_count: number,
    placeholder: string,
}
const props = defineProps<Props>()
const vForm = ref<VForm>()

const { update, remove } = useRequestStore()
</script>
<template>
    <tr scope="col">
        <td style="width: 10%;">{{ index + + props.base_count + 1 }}</td>
        <td style="width: 35%;">
            <VForm ref='vForm'>
                <VCol cols="12">
                    <VRow no-gutters>
                        <VTextField v-model="props.item.name" prepend-inner-icon="mdi-vector-intersection"
                            :placeholder="`${props.placeholder} 입력`" persistent-placeholder :rules="[requiredValidatorV2(props.item.name, '구간')]"
                            style="display: inline-block;" />
                    </VRow>
                </VCol>
            </VForm>
        </td>
        <td class="text-center" style="width: 25%;">
            <VCol class="d-flex gap-4">
                <VBtn type="button" color="default" variant="text" @click="update('/services/classifications', props.item, vForm, false)">
                    {{ props.item.id == 0 ? "추가" : "수정" }}
                    <VIcon end icon="tabler-pencil" />
                </VBtn>
                <VBtn type="button" color="default" variant="text" v-if="props.item.id" @click="remove('/services/classifications', props.item, false)">
                    삭제
                    <VIcon end icon="tabler-trash" />
                </VBtn>
                <VBtn type="button" color="default" variant="text" v-else @click="props.item.id = -1">
                    입력란 제거
                    <VIcon end icon="tabler-trash" />
                </VBtn>
            </VCol>
        </td>
    </tr>
</template>
