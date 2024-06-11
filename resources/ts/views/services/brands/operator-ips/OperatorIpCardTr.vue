<script lang="ts" setup>
import { useRequestStore } from '@/views/request'
import type { OperatorIp } from '@/views/types'
import { VCol, VForm } from 'vuetify/components'

interface Props {
    item: OperatorIp,
    index: number,
}
const vForm = ref<VForm>()
const props = defineProps<Props>()
const { update, remove } = useRequestStore()

</script>
<template>
    <tr>
        <td class='list-square'>{{ index + 1 }}</td>
        <td class='list-square'>
            <VForm ref="vForm">
                <VTextField v-model="props.item.enable_ip" type="text" placeholder="123.123.123.123" style="width: 300px;" 
                    prepend-inner-icon="material-symbols:bring-your-own-ip" />
            </VForm>
        </td>
        <td class='list-square'>
            <VCol class="d-flex gap-4" style="text-align: center;">
                <VBtn type="button" color="default" variant="text"
                    @click="update('/services/brands/operator-ips', props.item, vForm, false)">
                    {{ props.item.id == 0 ? "추가" : "수정" }}
                    <VIcon end icon="tabler-pencil" />
                </VBtn>
                <VBtn type="button" color="default" variant="text" v-if="props.item.id"
                    @click="remove('/services/brands/operator-ips', props.item, false)">
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
