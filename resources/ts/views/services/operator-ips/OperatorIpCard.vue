<script lang="ts" setup>
import BaseQuestionTooltip from '@/layouts/tooltips/BaseQuestionTooltip.vue';
import corp from '@/plugins/corp';
import { useRequestStore } from '@/views/request';
import OperatorIpCardTr from '@/views/services/operator-ips/OperatorIpCardTr.vue';
import type { OperatorIp } from '@/views/types';

interface Props {
    operator_ips: OperatorIp[],
    countdown_timer: string
}
const props = defineProps<Props>()

const { setNullRemove } = useRequestStore()
const token = <any>(inject('token'))

const addNewOperatorIpCard = () => {
    props.operator_ips.push(<OperatorIp>({
        id: 0,
        brand_id: corp.id,
        enable_ip: '',
        token: token,
    }))
}
watchEffect(() => {
    if(props.operator_ips !== undefined)
        setNullRemove(props.operator_ips)
})
</script>
<template>
    <section>
        <VCardTitle style="margin: 1em 0;">
            <VRow>
                <VCol md="6">
                    <BaseQuestionTooltip :location="'top'" :text="'운영자 접속허용 IP'" :content="'운영자는 등록된 IP에서만 로그인할 수 있습니다.'"/>
                </VCol>
                <VCol md="6">
                    <h5 style="text-align: end;">
                        <span>만료시간:</span>
                        <span id="countdown" class="text-primary" style="margin-inline-start: 0.5em;">{{ props.countdown_timer }}</span>
                    </h5>
                </VCol>
            </VRow>
        </VCardTitle>
        <VTable class="text-no-wrap" style=" min-width: 100%; margin-bottom: 1em;text-align: center;">
            <thead>
                <tr>
                    <th scope="col" class='list-square'>No.</th>
                    <th scope="col" class='list-square'>허용 IP</th>
                    <th scope="col" class='list-square'></th>
                </tr>
            </thead>
            <tbody>
                <OperatorIpCardTr v-for="(item, index) in props.operator_ips"
                    :key="item.id" style="margin-top: 1em;" :item="item" :index="index" />
            </tbody>
            <tfoot v-show="Boolean(operator_ips.length == 0)">
                <tr>
                    <td colspan="4" class="text-center">
                        운영사 정보를 추가하신 후 사용 가능합니다.
                    </td>
                </tr>
            </tfoot>
        </VTable>
        <VRow>
            <VCol class="d-flex gap-4">
                <VBtn type="button" style="margin-left: auto;" @click="addNewOperatorIpCard()">
                    로그인 허용 IP 신규추가
                    <VIcon end icon="tabler-plus" />
                </VBtn>
            </VCol>
        </VRow>
    </section>
</template>
<style scoped>
:deep(.v-table__wrapper) {
  block-size: auto !important;
}

</style>
