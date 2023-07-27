<script setup lang="ts">
import { Settle } from '@/views/types'
import { axios } from '@axios'
import { cloneDeep } from 'lodash'

interface Props {
    name: string,
    item: Settle,
    is_mcht: boolean
}

const props = defineProps<Props>()
const store = <any>(inject('store'))
const alert = <any>(inject('alert'))
const snackbar = <any>(inject('snackbar'))
const errorHandler = <any>(inject('$errorHandler'))

const settle = async () => {
    if (await alert.value.show('정말 ' + props.name + '님을(를) 정산 하시겠습니까?')) {
        const params = cloneDeep(store.params)
        const p = {
            id: props.item.id,
            acct_name: props.item.acct_name,
            acct_num: props.item.acct_num,
            acct_bank_code: props.item.acct_bank_code,
            acct_bank_name: props.item.acct_bank_name,
            total_amount: props.item.amount,            // 총 매출액
            cxl_amount: props.item.cxl.amount,          // 총 취소액
            appr_amount: props.item.appr.amount,        // 총 승인액
            deduct_amount: props.item.deduction.amount, // 추가차감금
            settle_amount: props.item.settle.amount,    // 정산액
            trx_amount: props.item.total_trx_amount,    // 총 거래 수수료(매출)
            level: props.is_mcht ? 10 : props.item.level,
            settle_fee: props.is_mcht ? props.item.settle_fee : 0,
        };
        try {
            const page = props.is_mcht ? 'merchandises' : 'salesforces'
            const r = await axios.post('/api/v1/manager/transactions/settle-histories/' + page, Object.assign(params, p))
            snackbar.value.show('성공하였습니다.', 'success')
            store.setTable()
        }
        catch (e: any) {
            snackbar.value.show(e.response.data.message, 'error')
            const r = errorHandler(e)
        }
    }
}
</script>
<template>
    <VBtn icon size="x-small" color="default" variant="text" :id="`item-${props.item.id}`">
        <VIcon size="22" icon="tabler-dots-vertical" />
        <VMenu activator="parent" width="230" :attach="`#item-${props.item.id}`">
            <VList>
                <VListItem value="settle" @click="settle()">
                    <template #prepend>
                        <VIcon size="24" class="me-3" icon="tabler-calculator" />
                    </template>
                    <VListItemTitle>정산하기</VListItemTitle>
                </VListItem>
            </VList>
        </VMenu>
    </VBtn>
</template>
<style scoped>
/deep/ .v-overlay__content {
  z-index: 99999999999 !important;
  inset-block-start: 4em !important;
  inset-inline-start: -19em !important;
}
</style>
