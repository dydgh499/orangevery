<script lang="ts" setup>
import BaseQuestionTooltip from '@/layouts/tooltips/BaseQuestionTooltip.vue'
import { useRequestStore } from '@/views/request'
import OperatorIpCardTr from '@/views/services/brands/operator-ips/OperatorIpCardTr.vue'
import type { Brand, OperatorIp } from '@/views/types'

interface Props {
    item: Brand,
}
const props = defineProps<Props>()

const { setNullRemove } = useRequestStore()

const addNewOperatorIpCard = () => {
    props.item.operator_ips.push(<OperatorIp>({
        id: 0,
        brand_id: props.item.id,
        enable_ip: '',
    }))
}
watchEffect(() => {
    if(props.item.operator_ips !== undefined)
        setNullRemove(props.item.operator_ips)
})
</script>
<template>
    <VCardTitle style="margin: 1em 0;">
        <BaseQuestionTooltip :location="'top'" :text="'운영자 로그인 허용 IP'" :content="'운영자는 등록된 IP에서만 로그인할 수 있습니다.<br>(해당 탭은 개발사밖에 확인할 수 없습니다.)'">
        </BaseQuestionTooltip>
    </VCardTitle>
    <VTable class="text-no-wrap" style=" min-width: 100%; margin-bottom: 1em;text-align: center;">
        <thead>
            <tr>
                <th scope="col" class='list-square'>No.</th>
                <th scope="col" class='list-square' style="width: 300px;">허용 IP</th>
                <th scope="col" class='list-square'></th>
            </tr>
        </thead>
        <tbody>
            <OperatorIpCardTr v-for="(item, index) in props.item.operator_ips"
                :key="item.id" style="margin-top: 1em;" :item="item" :index="index" />
        </tbody>
        <tfoot v-show="Boolean(props.item.id == 0)">
            <tr>
                <td colspan="4" class="text-center">
                    운영사 정보를 추가하신 후 사용 가능합니다.
                </td>
            </tr>
        </tfoot>
    </VTable>
    <VRow v-show="Boolean(props.item.id != 0)">
        <VCol class="d-flex gap-4">
            <VBtn type="button" style="margin-left: auto;" @click="addNewOperatorIpCard()">
                로그인 허용 IP 신규추가
                <VIcon end icon="tabler-plus" />
            </VBtn>
        </VCol>
    </VRow>
</template>
<style scoped>
:deep(.v-table__wrapper) {
  block-size: auto !important;
}
</style>
