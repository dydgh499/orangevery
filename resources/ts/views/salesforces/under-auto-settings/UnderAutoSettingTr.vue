<script lang="ts" setup>
import { useRequestStore } from '@/views/request'
import type { UnderAutoSetting } from '@/views/types'
import { VForm } from 'vuetify/components'

interface Props {
    item: UnderAutoSetting,
    index: number,
}
const vForm = ref<VForm>()
const props = defineProps<Props>()
const { update, remove } = useRequestStore()

</script>
<template>
    <tr>
        <td style="width: 10%;">{{ index + 1 }}</td>
        <td style="width: 40%;">
            <VForm ref="vForm">
                <VCol cols="12">
                    <VRow no-gutters>
                        <VTextField v-model="props.item.note" type="text" placeholder="별칭 입력"
                            prepend-inner-icon="twemoji-spiral-notepad" />
                    </VRow>
                </VCol>
            </VForm>
        </td>
        <td style="width: 40%;">
            <VForm ref="vForm">
                <VCol cols="12">
                    <VRow no-gutters>
                        <VTextField v-model="props.item.sales_fee" type="number" placeholder="수수료율 입력" suffix="%" />
                    </VRow>
                </VCol>
            </VForm>
        </td>
        <td class="text-center" style="width: 10%;">
            <VCol class="d-flex gap-4">
                <VBtn type="button" color="default" variant="text"
                    @click="update('/salesforces/under-auto-settings', props.item, vForm, false)">
                    {{ props.item.id == 0 ? "추가" : "수정" }}
                    <VIcon end icon="tabler-pencil" />
                </VBtn>
                <VBtn type="button" color="default" variant="text" v-if="props.item.id"
                    @click="remove('/salesforces/under-auto-settings', props.item, false)">
                    삭제
                    <VIcon end icon="tabler-trash" />
                </VBtn>
            </VCol>
        </td>
    </tr>
</template>
<style scoped>
td {
  padding: 0 !important;
}
</style>
