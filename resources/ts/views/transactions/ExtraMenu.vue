<script setup lang="ts">
import type { SalesSlip, CancelPay } from '@/views/types'
import { axios, getUserLevel, user_info } from '@axios'

interface Props {
    item: SalesSlip,
}

const props = defineProps<Props>()

const alert = <any>(inject('alert'))
const snackbar = <any>(inject('snackbar'))
const errorHandler = <any>(inject('$errorHandler'))

const salesslip = <any>(inject('salesslip'))
const cancelTran = <any>(inject('cancelTran'))
const router = useRouter()

const complaint = () => {
    const params = {
        pg_id: props.item.pg_id,
        mcht_id: props.item.mcht_id,
        appr_dt: props.item.trx_dt?.toString(),
        appr_num: props.item.appr_num,
        issuer: props.item.issuer,
        cust_name: props.item.buyer_name,
        phone_num: props.item.buyer_phone,
        tid: props.item.tid,
    }
    router.push({
        path: '/complaints/create',
        query: params,
    })
}
const payCanceled = async() => {
    if(await alert.value.show('정말 상위 PG사를 통해 결제를 취소하시겠습니까?')) {
        const params:CancelPay = {
            pmod_id: props.item.pmod_id as number,
            amount: props.item.amount,
            trx_id: props.item.trx_id,
            only: false,
        }
        try {
            const r = await axios.post('/api/v1/manager/transactions/pay-cancel', params)
            snackbar.value.show('성공하였습니다.', 'success')
        }
        catch (e: any) {
            snackbar.value.show(e.response.data.message, 'error')
            const r = errorHandler(e)
        }
    }
}
</script>
<template>
    <VBtn icon size="x-small" color="default" variant="text" :id="`item-${props.item.tid}`">
        <VIcon size="22" icon="tabler-dots-vertical" />
        <VMenu activator="parent" width="230" :attach="`#item-${props.item.tid}`">
            <VList>
                <VListItem value="saleslip" @click="salesslip.show(props.item)">
                    <template #prepend>
                        <VIcon size="24" class="me-3" icon="tabler:receipt" />
                    </template>
                    <VListItemTitle>매출전표</VListItemTitle>
                </VListItem>
                <VListItem value="complaint" @click="complaint()" v-if="getUserLevel() >= 35">
                    <template #prepend>
                        <VIcon size="24" class="me-3" icon="ic-round-sentiment-dissatisfied" />
                    </template>
                    <VListItemTitle>민원처리</VListItemTitle>
                </VListItem>
                <VListItem value="cancelTrans" @click="cancelTran.show(props.item)" v-show="props.item.is_cancel == false" v-if="getUserLevel() >= 35">
                    <template #prepend>
                        <VIcon size="24" class="me-3" icon="tabler:device-tablet-cancel" />
                    </template>
                    <VListItemTitle>취소매출생성</VListItemTitle>
                </VListItem>
                <VListItem value="cancel" @click="payCanceled()" v-show="props.item.is_cancel == false" v-if="getUserLevel() == 10 || getUserLevel() >= 35">
                    <template #prepend>
                        <VIcon size="24" class="me-3" icon="tabler:world-cancel" />
                    </template>
                    <VListItemTitle>결제취소하기</VListItemTitle>
                </VListItem>
            </VList>
        </VMenu>
    </VBtn>
</template>
<style scoped>
:deep(.v-overlay__content) {
  z-index: 99999999999 !important;
  inset-block-start: 4em !important;
  inset-inline-start: -19em !important;
}
</style>
