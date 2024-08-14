<script setup lang="ts">
import router from '@/router'
import CardLayout from '@/views/quick-view/CardLayout.vue'
import Recent30DaysContentOverview from '@/views/quick-view/Recent30DaysContentOverview.vue'
import Recent30DaysRankOverview from '@/views/quick-view/Recent30DaysRankOverview.vue'
import SettleContentOverview from '@/views/quick-view/SettleContentOverview.vue'
import SettleContentSkeleton from '@/views/quick-view/SettleContentSkeleton.vue'
import { useQuickViewStore } from '@/views/quick-view/useStore'
import type { MchtRecentTransactions } from '@/views/types'
import { axios, getUserLevel, user_info } from '@axios'

const transactions = ref(<MchtRecentTransactions>({}))
const is_skeleton = ref(true)
const { hands } = useQuickViewStore()


const my_level = getUserLevel()
const payShow  = <any>(inject('payShow'))

if(my_level >= 35)  //본사
    router.replace('dashboards')
else {
    axios.get('/api/v1/quick-view?level='+my_level)
        .then(r => { transactions.value = r.data as MchtRecentTransactions; })
        .catch(e => { console.log(e) })
}

const toHandPayLink = () => {
    payShow.value.show(hands[0])
}
const toComplaintLink = () => {
    router.push('complaints')
}

/*
const alert = <any>(inject('alert'))
const snackbar = <any>(inject('snackbar'))
const amount = ref(0)
const able_balance = ref(0)

const getWithdrawAbleAmount = async() => {
    const r = await get('/api/v1/quick-view/withdraw-able-amount', {})
    able_balance.value = Number(r.data.profit) - r.data.withdraw_fee
}

const isBrightFix = () => {
    return (corp.id == 14 || corp.id == 12 || corp.id == 30) ? true : false
}

const requestWithdraw = async() => {
    if(able_balance.value >= amount.value) {
        if(amount.value) {
            if(await alert.value.show('정말 '+amount.value+'원을 출금하시겠습니까?')) {
                const r = await post('/api/v1/bf/withdraws', {
                    withdraw_amount: amount.value,
                })
                if(r.status == 201)
                    getWithdrawAbleAmount()
            }
        }
        else
            snackbar.value.show('출금 금액을 입력해주세요.', 'warning')
    }
    else
        snackbar.value.show('출금 가능 금액을 초과하였습니다.', 'warning')
}
*/

watchEffect(() => {
    if(Object.keys(transactions.value).length)
        is_skeleton.value = false
    /*
        if(getUserLevel() == 10 && user_info.value.use_collect_withdraw)
            getWithdrawAbleAmount()
    */
})
</script>
<template>
    <section>
        <VRow>
            <CardLayout :padding="true">
                <template #content>
                    <VCol>
                        <div style="text-align: center;">
                            <span class="text-primary font-weight-bold">{{ user_info.user_name }}</span>
                            <span style="font-size: 0.9em;">님 안녕하세요 !</span>
                        </div>
                    </VCol>
                    <VCol cols="12" style="text-align: center;">
                        <VBtn variant="tonal" @click="router.push('/posts?type=0')" class="shortcut-button">
                            공지사항
                            <VIcon end icon="svg-spinners:bars-scale-middle" color="primary" />
                        </VBtn>
                        <VBtn variant="tonal" @click="router.push('/posts?type=2')" class="shortcut-button">
                            1:1 문의
                            <VIcon end icon="twemoji:adhesive-bandage" />
                        </VBtn>
                        <VBtn variant="tonal" @click="toHandPayLink()" class="shortcut-button"
                            v-if="hands.length > 0 && hands[0].show_pay_view">
                            수기결제
                            <VIcon end icon="fluent-payment-32-regular" />
                        </VBtn>
                        <VBtn variant="tonal" @click="toComplaintLink()" class="shortcut-button" color="error" v-if="getUserLevel() === 10">
                            민원관리
                            <VIcon end icon="ic-round-sentiment-dissatisfied" />
                        </VBtn>
                    </VCol>
                </template>
            </CardLayout>
            <template v-if="is_skeleton">
                <CardLayout v-for="(transaction, key) in 3" :key="key" :padding="true">
                    <template #content>
                        <SettleContentSkeleton/>
                    </template>
                </CardLayout>
            </template>
            <template v-else>
                <CardLayout v-for="(transaction, key) in transactions.monthly" :key="key" :padding="true">
                    <template #content>
                        <SettleContentOverview :transaction="transaction" :date="(key as string)"/>
                    </template>
                </CardLayout> 
            </template>
            <CardLayout :padding="false">
                <template #content>
                    <Recent30DaysContentOverview :transactions="transactions.daily" />
                </template>
            </CardLayout>
            <CardLayout :padding="false" v-if="getUserLevel() > 10">
                <template #content>
                    <Recent30DaysRankOverview :transactions="transactions.mchts" />
                </template>
            </CardLayout>
        </VRow>
    </section>
</template>
<style scoped>
.shortcut-button {
  margin: 0.5em;
}

:deep(.v-table__wrapper) {
  block-size: auto !important;
}
</style>
