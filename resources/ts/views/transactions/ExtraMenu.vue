<script setup lang="ts">
import router from '@/router'
import { useRequestStore } from '@/views/request'
import { notiSendHistoryInterface, realtimeHistoryInterface } from '@/views/transactions/transactions'
import type { MchtBlacklist, SalesSlip } from '@/views/types'
import { getUserLevel, pay_token } from '@axios'
import { StatusColors } from '@core/enums'
import corp from '@corp'

interface Props {
    item: SalesSlip,
}

const formatTime = <any>(inject('$formatTime'))

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
const cancelPart = <any>(inject('cancelPart'))
const mchtBlackListDlg = <any>(inject('mchtBlackListDlg'))
const notiSendHistoriesDialog = <any>(inject('notiSendHistoriesDialog'))
const realtimeHistoryDialog = <any>(inject('realtimeHistoryDialog'))

const { notiSend } = notiSendHistoryInterface()
const { 
    realtimeResult, realtimeRetryAble, realtimeRetry, 
    isRealtimeTransaction, singleDepositCancelJobReservation 
} = realtimeHistoryInterface(formatTime)

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
    const r = await realtimeRetry(props.item)
    if(r) {
        if(r.status == 201) {
            store.setChartProcess()
            store.setTable()
        }
    }
}

const payCanceled = async () => {
    if(corp.pv_options.paid.use_part_cancel) {
        const amount = await cancelPart.value.show(props.item.amount)
        if(amount == 0)
            return
        else
            props.item.amount = amount
    }

    if (await alert.value.show('정말 PG사를 통해 결제를 취소하시겠습니까?')) {
        const params = <any>({
            pmod_id: props.item.pmod_id,
            mcht_id: props.item.mcht_id,
            amount: props.item.amount,
            trx_id: props.item.trx_id,
            only: false,
        })
        if(getUserLevel() >= 35)
            params['operater_access_token'] = pay_token.value
        try {
            const r = await post('/api/v1/transactions/pay-cancel', params)
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

const blacklist = () => {
    mchtBlackListDlg.value.show(<MchtBlacklist>{
        id: 0,
        card_num: props.item.card_num
    })
}
const isCancelSafeDate = () => {
    if(getUserLevel() === 10) {
        if(props.item.cxl_type === -1)
            return true
        else if(props.item.cxl_type === 1) {
            const able_at = (new Date(props.item.trx_dttm as string)).getTime() + (5 * 60000)
            const offset_at = able_at - new Date() 
            return offset_at > 0 ? true : false
        }
        else if(props.item.cxl_type === 2 && props.item.trx_dt == formatDate(new Date()))
            return true
        else if(props.item.cxl_type === 3)
            return props.item.mcht_settle_id ? false : true
    }
    else if(getUserLevel() >= 35)
        return true
    return false
}

const isUseCancelDeposit = () => {
    return getUserLevel() >= 35 && props.item.is_cancel
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
                <VListItem value="blacklist" @click="blacklist()" v-if="getUserLevel() >= 35 && corp.pv_options.paid.use_mcht_blacklist">
                    <template #prepend>
                        <VIcon size="24" class="me-3" icon="arcticons:callsblacklist" />
                    </template>
                    <VListItemTitle>블랙리스트 등록</VListItemTitle>
                </VListItem>
                <VListItem value="retry-realtime-deposit" class="retry-realtime-deposit" @click="retryDeposit()"
                    v-if="isRealtimeTransaction() && realtimeRetryAble(props.item)">
                    <template #prepend>
                        <VIcon size="24" class="me-3" icon="fa6-solid:money-bill-transfer" />
                    </template>
                    <VListItemTitle>재이체</VListItemTitle>
                </VListItem>
                <VListItem value="retry-realtime-deposit" class="single-deposit-cancel-job" @click="singleDepositCancelJobReservation([props.item.id])"
                    v-if="isRealtimeTransaction() && realtimeResult(props.item) === StatusColors.Primary && getUserLevel() >= 35">
                    <template #prepend>
                        <VIcon size="24" class="me-3" icon="material-symbols:free-cancellation-outline" />
                    </template>
                    <VListItemTitle>이체예약취소</VListItemTitle>
                </VListItem>
                <VListItem value="noti" class="noti" @click="notiSend([props.item.id])"
                    v-if="corp.pv_options.paid.use_noti">
                    <template #prepend>
                        <VIcon size="24" class="me-3" icon="emojione:envelope" />
                    </template>
                    <VListItemTitle>노티전송</VListItemTitle>
                </VListItem>
                <VListItem value="noti-histories" class="noti" @click="notiSendHistoriesDialog.show(props.item)"
                    v-if="corp.pv_options.paid.use_noti">
                    <template #prepend>
                        <VIcon size="24" class="me-3" icon="tabler:history" />
                    </template>
                    <VListItemTitle>노티 발송이력</VListItemTitle>
                </VListItem>
                <VListItem value="realtime-histories" @click="realtimeHistoryDialog.show(props.item)"
                    v-if="isRealtimeTransaction()">
                    <template #prepend>
                        <VIcon size="24" class="me-3" icon="tabler:history" />
                    </template>
                    <VListItemTitle>실시간 이체이력</VListItemTitle>
                </VListItem>
                <VListItem value="cancelTrans" @click="cancelTran.show(props.item)"
                    v-if="getUserLevel() >= 35 && props.item.is_cancel == 0">
                    <template #prepend>
                        <VIcon size="24" class="me-3" icon="tabler:device-tablet-cancel" />
                    </template>
                    <VListItemTitle>취소매출생성</VListItemTitle>
                </VListItem>
                <VListItem value="cancel" class="pg-cancel" @click="payCanceled()"
                    v-if="isCancelSafeDate() && props.item.is_cancel == 0 && realtimeResult(props.item) != StatusColors.Success">
                    <template #prepend>
                        <VIcon size="24" class="me-3" icon="tabler:world-cancel" />
                    </template>
                    <VListItemTitle>{{ getUserLevel() >= 35 ? '결제취소(운영자 권한)': '결제취소하기'}}</VListItemTitle>
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
