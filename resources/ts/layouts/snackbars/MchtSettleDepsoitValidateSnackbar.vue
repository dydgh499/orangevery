<script setup lang="ts">
import { useRequestStore } from '@/views/request';
import { useStore } from '@/views/services/pay-gateways/useStore';
import type { Popup } from '@/views/types';
import { PopupEvent } from '@core/utils/popup';


const formatDate = <any>(inject('$formatDate'))

const settle_cookie = ref(<Popup>({
    id: formatDate(new Date()),
    visible: false,
    is_hide: false,
}))
const { setOpenStatus, init } = PopupEvent('settle/merchandises/'+settle_cookie.value.id)
const { finance_vans } = useStore()
const { post } = useRequestStore()

const deposit_snackbar = ref(false)
const deposit_status = ref(false)
const settle_info = ref({
    settle_amount: 0,
    deposit_amount: 0,
    profit_amount: 0,
})

const show = async (params: any) => {
    init(settle_cookie.value)
    const current = new Date()
    const date = formatDate(current)
    const hour = current.getHours()
    const day  = current.getDay()
    
    if(day !== 0 && day !== 6 && hour >= 13 && hour <= 18) {
        if(settle_cookie.value.visible) {
            const fin_ids = finance_vans.filter(f => f.is_agency_van === 1).map(f => f.id)
            if(fin_ids.length > 0) {
                const r = await post('/api/v1/manager/transactions/settle/merchandises/deposit-validate', {
                    s_dt: date,
                    e_dt: date,
                    level: 10,
                    search: '',
                    use_collect_withdraw: params.use_collect_withdraw,
                    use_realtime_deposit: params.use_realtime_deposit,
                    page: 1,
                    page_size: 1,
                    fin_ids: fin_ids
                }, false)
                if(settle_info.value.settle_amount !== 0) {
                    deposit_snackbar.value = true
                    if(r.data.deposit_amount) {
                        settle_info.value = r.data
                        deposit_status.value = true
                    }
                }
                else {
                    settle_cookie.value.is_hide = true
                    setOpenStatus(settle_cookie.value)
                }
            }
        }
    }
}

defineExpose({
    show
});
</script>
<template>
    <VSnackbar v-model="deposit_snackbar"
        location="top end"
        transition="scroll-y-reverse-transition"
        vertical
        color="primary"
        :timeout="600000">
        <template v-if="deposit_status">
            {{ formatDate(new Date()) }} 총 입금/정산액
            <br><br>
            입금액: <span>{{ settle_info.deposit_amount.toLocaleString() }} &#8361;</span>
            <br>
            정산액: <span>{{ settle_info.settle_amount.toLocaleString() }} &#8361;</span>
            <br>
            <h4 :class="settle_info.profit_amount > 0 ? 'text-success' : 'text-error'">운영사 이익금: {{ settle_info.profit_amount.toLocaleString() }} &#8361;</h4>
        </template>
        <template v-else="settle_cookie.is_hide">
            오늘 정산을 완료하였습니다! 🎉
        </template>
        <template v-else>
            아직 오늘 입금금액이 들어오지 않았어요! 😥 
        </template>
        <template #actions>
            <VBtn
                color="error"
                @click="deposit_snackbar = false"
            >
                <b>닫기</b>
            </VBtn>
        </template>
    </VSnackbar>
</template>
