<script setup lang="ts">
import type { Transaction, Merchandise, PayModule, PaySection } from '@/views/types'
import { axios } from '@axios'
import { defineProps, ComponentPublicInstance } from 'vue'

interface Props {
    item: Transaction,
}

const props = defineProps<Props>()
const salesSilp = <any>(inject('salesSilp'))
const router = useRouter()

const complaint = async () => {
    router.push('/complaints/create?')
}
</script>
<template>
    <VBtn icon size="x-small" color="default" variant="text">
        <VIcon size="22" icon="tabler-dots-vertical" />
        <VMenu activator="parent">
            <VList>
                <VListItem value="saleslip" @click="salesSilp.show(props.item)">
                    <template #prepend>
                        <VIcon size="24" class="me-3" icon="tabler:receipt" />
                    </template>
                    <VListItemTitle>매출전표</VListItemTitle>
                </VListItem>
                <VListItem value="complaint" @click="complaint()">
                    <template #prepend>
                        <VIcon size="24" class="me-3" icon="ic-round-sentiment-dissatisfied" />
                    </template>
                    <VListItemTitle>민원처리</VListItemTitle>
                </VListItem>
                <VListItem value="complaint" @click="">
                    <template #prepend>
                        <VIcon size="24" class="me-3" icon="tabler:device-tablet-cancel" />
                    </template>
                    <VListItemTitle>취소매출생성</VListItemTitle>
                </VListItem>
            </VList>
        </VMenu>
    </VBtn>
</template>
