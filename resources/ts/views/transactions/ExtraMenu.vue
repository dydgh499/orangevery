<script setup lang="ts">
import { isRetryAble, realtimeResult } from '@/views/transactions/useStore'
import { useRequestStore } from '@/views/request'
import type { SalesSlip } from '@/views/types'
import { getUserLevel } from '@axios'
import { StatusColors } from '@core/enums'
import corp from '@corp'
import router from '@/router'

interface Props {
    item: SalesSlip,
}

const { post } = useRequestStore()
const props = defineProps<Props>()

const store = <any>(inject('store'))
const alert = <any>(inject('alert'))
const snackbar = <any>(inject('snackbar'))
const errorHandler = <any>(inject('$errorHandler'))
const formatDate = <any>(inject('$formatDate'))

const salesslip = <any>(inject('salesslip'))
const cancelTran = <any>(inject('cancelTran'))
const cancelDeposit = <any>(inject('cancelDeposit'))
const realtimeHistories = <any>(inject('realtimeHistories'))

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
const retryDeposit = async () => {
    if (await alert.value.show('정말 해당 가맹점의 거래건을 재이체(정산) 하시겠습니까?')) {
        const params = {
            'trx_id': props.item.id,
            'mid': props.item.mid,
            'tid': props.item.tid,
        }
        const r = await post('/api/v1/manager/transactions/settle-histories/merchandises/single-settle-deposit', params, true)
        if(r.status == 201) {
            store.setChartProcess()
            store.setTable()
        }
    }
}

const payCanceled = async () => {
    if (await alert.value.show('정말 상위 PG사를 통해 결제를 취소하시겠습니까?')) {
        const params = {
            pmod_id: props.item.pmod_id,
            mcht_id: props.item.mcht_id,
            amount: props.item.amount,
            trx_id: props.item.trx_id,
            only: false,
        }
        try {
            const r = await post('/api/v1/manager/transactions/pay-cancel', params)
            if(r.status === 201)
                snackbar.value.show('성공하였습니다.', 'success')
            else
                snackbar.value.show(r.data.message, 'error')
        }
        catch (e: any) {
            snackbar.value.show(e.response.data.message, 'error')
            const r = errorHandler(e)
        }
    }
}
const isCancelSafeDate = () => {
    return getUserLevel() == 10 && props.item.trx_dt == formatDate(new Date())
}
const isRealtimeTransaction = () => {
    return getUserLevel() >= 35 && corp.pv_options.paid.use_realtime_deposit
}
const isUseCancelDeposit = () => {
    return getUserLevel() >= 35 && corp.pv_options.paid.use_cancel_deposit && props.item.is_cancel && !props.item.mcht_settle_id
}
</script>
<template>
    <VBtn icon size="x-small" color="default" variant="text">
        <VIcon size="22" icon="tabler-dots-vertical" />
        <VMenu activator="parent" width="250">
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
                <VListItem value="retry-realtime-deposit" class="retry-realtime-deposit" @click="retryDeposit()"
                    v-if="isRealtimeTransaction() && isRetryAble(props.item)">
                    <template #prepend>
                        <VIcon size="24" class="me-3" icon="fa6-solid:money-bill-transfer" />
                    </template>
                    <VListItemTitle>재이체</VListItemTitle>
                </VListItem>
                <VListItem value="realtime-histories" @click="realtimeHistories.show(props.item)"
                    v-if="isRealtimeTransaction()">
                    <template #prepend>
                        <VIcon size="24" class="me-3" icon="tabler:history" />
                    </template>
                    <VListItemTitle>실시간 상세이력</VListItemTitle>
                </VListItem>
                <VListItem value="cancelTrans" @click="cancelTran.show(props.item)"
                    v-if="getUserLevel() >= 35 && props.item.is_cancel == false">
                    <template #prepend>
                        <VIcon size="24" class="me-3" icon="tabler:device-tablet-cancel" />
                    </template>
                    <VListItemTitle>취소매출생성</VListItemTitle>
                </VListItem>
                <VListItem value="cancel" class="pg-cancel" @click="payCanceled()"
                    v-if="(isCancelSafeDate() || getUserLevel() >= 35) && props.item.is_cancel == false && realtimeResult(props.item) != StatusColors.Success">
                    <template #prepend>
                        <VIcon size="24" class="me-3" icon="tabler:world-cancel" />
                    </template>
                    <VListItemTitle>결제취소하기</VListItemTitle>
                </VListItem>
                <VListItem value="cancel-deposit" @click="cancelDeposit.show(props.item)"
                    v-if="isUseCancelDeposit()">
                    <template #prepend>
                        <VIcon size="24" class="me-3" icon="material-symbols:account-balance" />
                    </template>
                    <VListItemTitle>입금내역등록</VListItemTitle>
                </VListItem>
            </VList>
        </VMenu>
    </VBtn>
</template>
