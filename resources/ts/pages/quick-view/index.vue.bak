<script setup lang="ts">
import router from '@/router'
import CardLayout from '@/views/quick-view/CardLayout.vue'
import Recent30DaysContentOverview from '@/views/quick-view/Recent30DaysContentOverview.vue'
import Recent30DaysRankOverview from '@/views/quick-view/Recent30DaysRankOverview.vue'
import SettleContentOverview from '@/views/quick-view/SettleContentOverview.vue'
import SettleContentSkeleton from '@/views/quick-view/SettleContentSkeleton.vue'
import { useQuickViewStore } from '@/views/quick-view/useStore'
import { useRequestStore } from '@/views/request'
import type { MchtRecentTransactions } from '@/views/types'
import { getUserLevel, user_info } from '@axios'
import corp from '@corp'

const transactions = ref(<MchtRecentTransactions>({}))
const is_skeleton = ref(true)
const { get, post } = useRequestStore()
const { hands, getEncryptParams } = useQuickViewStore()

const alert = <any>(inject('alert'))
const snackbar = <any>(inject('snackbar'))
const my_level = getUserLevel()
const amount = ref(0)
const able_balance = ref(0)

if(my_level >= 35)  //본사
    router.replace('dashboards')
else {
    get('/api/v1/quick-view?level='+my_level)
        .then(r => { transactions.value = r.data as MchtRecentTransactions; })
        .catch(e => { console.log(e) })
}

const toHandPayLink = () => {    
    location.href = '/pay/hand?e=' + getEncryptParams(hands[0])
}

const getWithdrawAbleAmount = async() => {
    const r = await get('/api/v1/quick-view/withdraw-able-amount', {})
    able_balance.value = Number(r.data.profit) - r.data.withdraw_fee
}
const isBrightFix = () => {
    // temp function
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

const toComplaintLink = () => {
    router.push('complaints')
}

watchEffect(() => {
    if(Object.keys(transactions.value).length)
        is_skeleton.value = false
    if(getUserLevel() == 10 && user_info.value.use_collect_withdraw)
        getWithdrawAbleAmount()
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
            <CardLayout :padding="true" v-if="getUserLevel() == 10 && user_info.use_collect_withdraw && isBrightFix()">
                <template #content>
                    <VCol class="d-flex justify-space-between small-font">
                        <div>
                            <div>
                                <span>출금가능 금액</span>                                
                            </div>
                            <div style="font-weight: bold;">
                                {{ able_balance.toLocaleString() }}원
                            </div>
                        </div>
                        <VAvatar rounded variant="tonal" icon="fa6-solid:money-bill-transfer" />
                    </VCol>
                    <VCol style="padding-top: 0;" class="d-flex justify-space-between small-font">
                        <div>
                            <div>
                                <span>출금금액 입력</span>
                            </div>
                            <div style="display: inline-flex;">
                                <VTextField v-model="amount" type="number" suffix="￦" placeholder="출금금액 입력"
                                    prepend-inner-icon="ic:outline-price-change" />
                                <VBtn type="button" @click="requestWithdraw()" style="margin-left: 0.5em;">
                                    출금하기
                                    <VIcon end icon="fa6-solid:money-bill-transfer" />
                                </VBtn>
                            </div>
                        </div>
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
                        <SettleContentOverview :transaction="transaction" :date="(key as string)">
                        </SettleContentOverview>
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
