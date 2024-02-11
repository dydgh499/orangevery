<script lang="ts" setup>
import { useRequestStore } from '@/views/request'
import type { DifferentSettlementInfo } from '@/views/types'
import { useStore } from '@/views/services/pay-gateways/useStore'
import { VCol, VForm } from 'vuetify/components'

interface Props {
    item: DifferentSettlementInfo,
    index: number,
}
const vForm = ref<VForm>()
const props = defineProps<Props>()
const { update, remove } = useRequestStore()
const { pg_companies } = useStore()

</script>
<template>
    <tr>
        <td class='list-square'>{{ index + 1 }}</td>
        <td class='list-square' >
            <VForm ref="vForm">
                <VSelect :menu-props="{ maxHeight: 400 }" v-model="props.item.pg_type"
                    :items="pg_companies" prepend-inner-icon="ph-buildings" label="PG사 선택"
                    item-title="name" item-value="id" single-line style="width: 250px;"/>
            </VForm>
        </td>
        <td class='list-square'>
            <VTextField v-model="props.item.rep_mid" type="text" placeholder="대표가맹점 MID" style="width: 150px;" />
        </td>
        <td class='list-square'>
            <VTextField v-model="props.item.sftp_id" type="text" placeholder="SFTP ID" style="width: 150px;" />
        </td>
        <td class='list-square'>
            <VTextField v-model="props.item.sftp_password" type="text" placeholder="SFTP PW" style="width: 150px;" />
        </td>
        <td class='list-square'>
            <VCol class="d-flex gap-4" style="text-align: center;">
                <VBtn type="button" color="default" variant="text"
                    @click="update('/services/brands/different-settlement-infos', props.item, vForm, false)">
                    {{ props.item.id == 0 ? "추가" : "수정" }}
                    <VIcon end icon="tabler-pencil" />
                </VBtn>
                <VBtn type="button" color="default" variant="text" v-if="props.item.id"
                    @click="remove('/services/brands/different-settlement-infos', props.item, false)">
                    삭제
                    <VIcon end icon="tabler-trash" />
                </VBtn>
            </VCol>
        </td>
    </tr>
</template>
