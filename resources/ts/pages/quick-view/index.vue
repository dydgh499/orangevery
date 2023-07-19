<script setup lang="ts">
import { axios, user_info, getUserLevel } from '@axios'
import CardLayout from '@/views/quick-view/CardLayout.vue'
import SettleContentOverview from '@/views/quick-view/SettleContentOverview.vue'
import Recent30DaysRankOverview from '@/views/quick-view/Recent30DaysRankOverview.vue'
import Recent30DaysContentOverview from '@/views/quick-view/Recent30DaysContentOverview.vue'
import type { MchtRecentTransaction } from '@/views/types'

const router = useRouter()
const transactions = ref(<MchtRecentTransaction>({}))

axios.get('/api/v1/quick-view')
    .then(r => { transactions.value = r.data as MchtRecentTransaction; })
    .catch(e => { console.log(e) })

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
                    <VCol>
                        <div class="d-flex justify-space-evenly">
                            <VBtn variant="tonal" @click="router.push('/posts?type=0')">
                                공지사항
                                <VIcon end icon="svg-spinners:bars-scale-middle" color="primary" />
                            </VBtn>
                            <VBtn variant="tonal" @click="router.push('/posts?type=2')">
                                1:1 문의
                                <VIcon end icon="twemoji:adhesive-bandage" />
                            </VBtn>
                        </div>
                    </VCol>
                </template>
            </CardLayout>
            <CardLayout v-for="(transaction, key) in transactions['month']" :key="key" :padding="true">
                <template #content>
                    <SettleContentOverview :transaction="transaction" :date="key">
                    </SettleContentOverview>
                </template>
            </CardLayout>
            <CardLayout :padding="false">
                <template #content>
                    <Recent30DaysContentOverview :transactions="transactions['day']" />
                </template>
            </CardLayout>
            <CardLayout :padding="false" v-if="getUserLevel() > 10">
                <template #content>
                    <Recent30DaysRankOverview :transactions="transactions['mcht_name']" />
                </template>
            </CardLayout>
        </VRow>
    </section>
</template>
