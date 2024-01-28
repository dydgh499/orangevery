<script lang="ts" setup>
import { useRequestStore } from '@/views/request'
import type { BeforeBrandInfo } from '@/views/types'
import { VCol, VForm } from 'vuetify/components'

interface Props {
    item: BeforeBrandInfo,
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
                <VTextField v-model="props.item.company_name" type="text" placeholder="운영사명 입력" style="width: 200px;"/>
            </VForm>
        </td>
        <td class='list-square'>
            <VTextField v-model="props.item.rep_name" type="text" placeholder="대표자명 입력" style="width: 150px;" />
        </td>
        <td class='list-square'>
            <VTextField v-model="props.item.phone_num" type="text" placeholder="휴대폰번호 입력" style="width: 150px;" />
        </td>
        <td class='list-square'>
            <VTextField v-model="props.item.business_num" type="text" placeholder="사업자번호 입력" style="width: 150px;" />
        </td>
        <td class='list-square'>
            <VTextField v-model="props.item.addr" type="text" placeholder="주소 입력" style="width: 200px;" />
        </td>
        <td class='list-square'>
            <VRow no-gutters style="width: 350px;">
                <VCol md="5">
                    <VTextField type="date" v-model="props.item.apply_s_dt" label="시작일 입력" single-line style="width: 150px;"/>
                </VCol>
                <span style="margin: 0 1em; line-height: 2.5em;">~</span>
                <VCol md="5">
                    <VTextField type="date" v-model="props.item.apply_e_dt" label="종료일 입력" single-line style="width: 150px;"/>
                </VCol>
            </VRow>
        </td>
        <td class='list-square'>
            <VCol class="d-flex gap-4">
                <VBtn type="button" color="default" variant="text"
                    @click="update('/services/brands/before-brand-infos', props.item, vForm, false)">
                    {{ props.item.id == 0 ? "추가" : "수정" }}
                    <VIcon end icon="tabler-pencil" />
                </VBtn>
                <VBtn type="button" color="default" variant="text" v-if="props.item.id"
                    @click="remove('/services/brands/before-brand-infos', props.item, false)">
                    삭제
                    <VIcon end icon="tabler-trash" />
                </VBtn>
            </VCol>
        </td>
    </tr>
</template>
