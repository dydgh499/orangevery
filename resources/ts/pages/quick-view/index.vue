<script setup lang="ts">
import corp from '@/plugins/corp'
import router from '@/router'
import CardLayout from '@/views/quick-view/CardLayout.vue'
import CollectWithdrawOverview from '@/views/quick-view/CollectWithdrawOverview.vue'
import Recent30DaysContentOverview from '@/views/quick-view/Recent30DaysContentOverview.vue'
import Recent30DaysRankOverview from '@/views/quick-view/Recent30DaysRankOverview.vue'
import SettleContentOverview from '@/views/quick-view/SettleContentOverview.vue'
import SettleContentSkeleton from '@/views/quick-view/SettleContentSkeleton.vue'
import { useQuickViewStore } from '@/views/quick-view/useStore'
import type { MchtRecentTransactions } from '@/views/types'
import { axios, getUserLevel, user_info } from '@axios'

const transactions = ref(<MchtRecentTransactions>({
    monthly : {},
    daily : [],
    mchts : [],
}))
const is_skeleton = ref(true)
const { getFirstModule } = useQuickViewStore()
const errorHandler = <any>(inject('$errorHandler'))

const my_level = getUserLevel()
const payShow  = <any>(inject('payShow'))

const isAbleCollectWithdraw = () => {
    if(corp.pv_options.paid.use_collect_withdraw && [12,14,31].includes(corp.id) === false)
        return getUserLevel() === 10 && user_info.value.use_collect_withdraw
    else
        return false
}

onMounted(() => {
    if(my_level) {
        if(my_level >= 35)  //본사
            router.replace('dashboards')
        else {
            axios.get('/api/v1/quick-view?level='+my_level)
                .then(r => { 
                    transactions.value = r.data as MchtRecentTransactions;
                    is_skeleton.value = false
                })
                .catch(e => { 
                    const r = errorHandler(e)
                })
        }
    }
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
                        <template v-if="getFirstModule().icon !== ''">
                            <VBtn variant="tonal" @click="payShow.show(getFirstModule().module)" class="shortcut-button">
                                {{ getFirstModule().title }}
                                <VIcon end :icon="getFirstModule().icon" />
                            </VBtn>
                        </template>
                        <VBtn variant="tonal" @click="router.push('complaints')" class="shortcut-button" color="error" v-if="getUserLevel() === 10">
                            민원관리
                            <VIcon end icon="ic-round-sentiment-dissatisfied" />
                        </VBtn>
                    </VCol>
                </template>
            </CardLayout>
            <template v-if="isAbleCollectWithdraw()">
                <CardLayout :padding="true">
                    <template #content>
                        <CollectWithdrawOverview :is_skeleton="is_skeleton"/>
                    </template>
                </CardLayout>
            </template>
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
