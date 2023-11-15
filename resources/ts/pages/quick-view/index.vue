<script setup lang="ts">
import { user_info, getUserLevel } from '@axios'
import { useRequestStore } from '@/views/request'
import CardLayout from '@/views/quick-view/CardLayout.vue'
import SettleContentOverview from '@/views/quick-view/SettleContentOverview.vue'
import SettleContentSkeleton from '@/views/quick-view/SettleContentSkeleton.vue'
import Recent30DaysRankOverview from '@/views/quick-view/Recent30DaysRankOverview.vue'
import Recent30DaysContentOverview from '@/views/quick-view/Recent30DaysContentOverview.vue'
import type { MchtRecentTransactions } from '@/views/types'
import router from '@/router'
import corp from '@corp'
import { useQuickVuewStore } from '@/views/quick-view/useStore'

const transactions = ref(<MchtRecentTransactions>({}))
const is_skeleton = ref(true)
const { get } = useRequestStore()
const { hands, getEncryptParams } = useQuickVuewStore()

const formatDate = <any>(inject('$formatDate'))
const my_level = getUserLevel()

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

const toWithDrawLink = () => {
    const date = new Date();
    const s_dt = new Date(date.getFullYear(), date.getMonth(), 1, 0, 0, 0)
    const e_dt = new Date(date.getFullYear(), date.getMonth() + 1, 0, 23, 59, 59)
    const url = "/transactions/settle/merchandises/part/"+user_info.value.id
    const params = {
        s_dt: formatDate(s_dt),
        e_dt: formatDate(e_dt),
        use_collect_withdraw: 1,
        level: 10,
        dev_use: Number(corp.pv_options.auth.levels.dev_use),
        page: 1,
        page_size: 20,
    }    
    router.push({
        path: url,
        query: params
    })
}

watchEffect(() => {
    if(Object.keys(transactions.value).length)
        is_skeleton.value = false
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
                            v-if="hands.length > 0">
                            수기결제
                            <VIcon end icon="fluent-payment-32-regular" />
                        </VBtn>
                        <VBtn variant="tonal" @click="toWithDrawLink()" class="shortcut-button"
                            v-if="user_info.use_collect_withdraw">
                            출금하기
                            <VIcon end icon="tabler-calculator" />
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
<style>
  .shortcut-button {
    margin: 0.5em;
  }
</style>
