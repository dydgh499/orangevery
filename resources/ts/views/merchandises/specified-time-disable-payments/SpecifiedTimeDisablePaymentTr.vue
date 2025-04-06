<script lang="ts" setup>
import { inputFormater } from '@/@core/utils/formatters';
import { useRequestStore } from '@/views/request';
import type { Options, SpecifiedTimeDisablePayment } from '@/views/types';
import { isAbleModiy } from '@axios';
import { VForm } from 'vuetify/components';

interface Props {
    item: SpecifiedTimeDisablePayment,
    index: number,
}
const vForm = ref<VForm>()
const props = defineProps<Props>()
const { update, remove } = useRequestStore()
const { formatTime } = inputFormater()

const disable_types = <Options[]>[
    {id:0, title: '결제금지'}, {id:1, title: '이체금지'}
]
const formatDisableStm = computed(() => {
    props.item.disable_s_tm = formatTime(props.item.disable_s_tm || "")
})
const formatDisableEtm = computed(() => {
    props.item.disable_e_tm = formatTime(props.item.disable_e_tm || "")    
})

</script>
<template>
    <tr>
        <td class='list-square'>{{ index + 1 }}</td>
        <td class='list-square'>
            <VCol cols="12">
                <VRow no-gutters>
                    <VSelect 
                        v-if="isAbleModiy(props.item.id)"
                        v-model="props.item.disable_type" 
                        variant='underlined'
                        :items="disable_types"
                        label="제한타입 설정" item-title="title" item-value="id" single-line 
                        style="min-width: 10em;"
                    />
                    <span v-else>
                        {{ disable_types.find(obj => obj.id === props.item.disable_type)?.title}}
                    </span>
                </VRow>
            </VCol>
        </td>
        <td class='list-square'>
            <VForm ref="vForm">
                <VCol cols="12">
                    <VRow no-gutters>
                        <VTextField 
                            v-if="isAbleModiy(props.item.id)"
                            v-model="props.item.disable_s_tm" 
                            placeholder="시작시간"
                            @input="formatDisableStm"
                            variant='underlined'
                        />
                        <span v-else>
                            {{ props.item.disable_s_tm }}
                        </span>
                    </VRow>
                </VCol>
            </VForm>
        </td>
        <td class='list-square'>
            <VCol cols="12">
                <VRow no-gutters>
                    <VTextField 
                        v-if="isAbleModiy(props.item.id)"
                        v-model="props.item.disable_e_tm" 
                        placeholder="종료시간"
                        @input="formatDisableEtm"
                        variant='underlined'
                    />
                    <span v-else>
                        {{ props.item.disable_e_tm }}
                    </span>
                </VRow>
            </VCol>
        </td>
        <td cclass='list-square' v-if="isAbleModiy(props.item.id)">
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
