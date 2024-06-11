<script lang="ts" setup>
import { useRequestStore } from '@/views/request'
import { useStore } from '@/views/services/pay-gateways/useStore'
import type { DifferentSettlementInfo } from '@/views/types'
import { VCol, VForm } from 'vuetify/components'

interface Props {
    item: DifferentSettlementInfo,
    index: number,
}
const vForm = ref<VForm>()
const props = defineProps<Props>()
const { update, remove } = useRequestStore()
const { pg_companies } = useStore()
const is_show = ref(false)

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
            <VTextField v-model="props.item.sftp_password" placeholder="SFTP PW" style="width: 150px;"
                :append-inner-icon="is_show ? 'tabler-eye' : 'tabler-eye-off'" :type="is_show ? 'text' : 'password'"
                persistent-placeholder @click:append-inner="is_show = !is_show" />
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
                <VBtn type="button" color="default" variant="text" v-else @click="props.item.id = -1">
                    입력란 제거
                    <VIcon end icon="tabler-trash" />
                </VBtn>
            </VCol>
        </td>
    </tr>
</template>
