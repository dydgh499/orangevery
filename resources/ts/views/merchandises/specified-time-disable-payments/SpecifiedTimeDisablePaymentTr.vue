<script lang="ts" setup>
import { useRequestStore } from '@/views/request'
import type { Options, SpecifiedTimeDisablePayment } from '@/views/types'
import { requiredValidatorV2 } from '@validators'
import { VForm } from 'vuetify/components'

interface Props {
    item: SpecifiedTimeDisablePayment,
    index: number,
}
const vForm = ref<VForm>()
const props = defineProps<Props>()
const { update, remove } = useRequestStore()
const disable_types = <Options[]>[
    {id:0, title: '결제금지'}, {id:1, title: '이체금지'}
]
</script>
<template>
    <tr>
        <td class='list-square'>{{ index + 1 }}</td>
        <td class='list-square'>
            <VCol cols="12">
                <VRow no-gutters>
                    <VSelect v-model="props.item.disable_type" :items="disable_types" prepend-inner-icon="tabler:world-cancel"
                        label="제한타입 설정" item-title="title" item-value="id" single-line />
                </VRow>
            </VCol>
        </td>
        <td class='list-square'>
            <VForm ref="vForm">
                <VCol cols="12">
                    <VRow no-gutters>
                        <VTextField v-model="props.item.disable_s_tm" type="time" 
                        :rules="[requiredValidatorV2(props.item.disable_s_tm, '시작시간')]"/>
                    </VRow>
                </VCol>
            </VForm>
        </td>
        <td class='list-square'>
            <VCol cols="12">
                <VRow no-gutters>
                    <VTextField v-model="props.item.disable_e_tm" type="time"
                    :rules="[requiredValidatorV2(props.item.disable_e_tm, '종료시간')]" />
                </VRow>
            </VCol>
        </td>
        <td cclass='list-square'>
            <VCol class="d-flex gap-4">
                <VBtn type="button" color="default" variant="text"
                    @click="update('/merchandises/specified-time-disable-payments', props.item, vForm, false)">
                    {{ props.item.id == 0 ? "추가" : "수정" }}
                    <VIcon end icon="tabler-pencil" />
                </VBtn>
                <VBtn type="button" color="default" variant="text" v-if="props.item.id"
                    @click="remove('/merchandises/specified-time-disable-payments', props.item, false)">
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
<style scoped>
td {
  padding: 0 !important;
}
</style>
