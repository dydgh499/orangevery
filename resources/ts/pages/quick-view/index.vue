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
const { getPayMenuIcon, payment_modules } = useQuickViewStore()

const my_level = getUserLevel()
const payShow  = <any>(inject('payShow'))
const errorHandler = <any>(inject('$errorHandler'))

const getPaymentModuleNote = computed(() => {
    if(payment_modules.length) {
        if(payment_modules[0].module_type === 1)
            return '수기결제'
        else if(payment_modules[0].module_type === 2)
            return '인증결제'
        else if(payment_modules[0].module_type === 3)
            return '간편결제'
    }
    return ''
})

const getPaymentModuleIcon = computed(() => {
    if(payment_modules.length)
        return getPayMenuIcon(payment_modules[0].module_type)
    else
        return ''
})

onMounted(() => {
    if(my_level >= 35)  //본사
        router.replace('dashboards')
    else {
        axios.get('/api/v1/quick-view?level='+my_level)
            .then(r => { transactions.value = r.data as MchtRecentTransactions; })
            .catch(e => { 
                const r = errorHandler(e)
            })
    }

    watchEffect(() => {
        if(Object.keys(transactions.value).length)
            is_skeleton.value = false
    })
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
                        <VBtn variant="tonal" @click="payShow.show(payment_modules[0])" class="shortcut-button"
                            v-if="payment_modules.length > 0 && payment_modules[0].pay_window_secure_level">
                            {{ getPaymentModuleNote }}
                            <VIcon end :icon="getPaymentModuleIcon" />
                        </VBtn>
                        <VBtn variant="tonal" @click="router.push('complaints')" class="shortcut-button" color="error" v-if="getUserLevel() === 10">
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
