<script setup lang="ts">
import { axios, user_info } from '@axios'
import CardLayout from '@/views/quick-view/CardLayout.vue'
import SettleContentOverview from '@/views/quick-view/SettleContentOverview.vue'
import type { MchtRecentTransaction } from '@/views/types'

const router = useRouter()
const transactions = ref(<MchtRecentTransaction[]>([]))

axios.get('api/v1/quick-view')
.then(r => { /*transactions.value = r.data.content as MchtRecentTransaction[]*/ })
.catch(e => { console.log(e) })

</script>
<template>
    <section>
        <VRow>
            <CardLayout>
                <template #content>
                    <VCol>
                    <div style="text-align: center;">
                        <span class="text-primary font-weight-bold">{{ user_info.user_name }}</span>님 안녕하세요 !
                    </div>
                </VCol>
                <VCol>
                    <div class="d-flex justify-space-evenly">
                        <VBtn @click="router.push('/posts?type=0')">
                            공지사항
                            <VIcon end icon="fxemoji-notepage" />
                        </VBtn>
                        <VBtn variant="tonal" @click="router.push('/posts?type=2')">
                            1:1 문의
                            <VIcon end icon="clarity:talk-bubbles-line" />
                        </VBtn>
                    </div>
                </VCol>
                </template>
            </CardLayout>
            <CardLayout v-for="(transaction, key) in transactions" :key="key">
                <template #content>
                    <SettleContentOverview :transaction="transaction" />
                </template>
            </CardLayout>
        </VRow>
    </section>
</template>
