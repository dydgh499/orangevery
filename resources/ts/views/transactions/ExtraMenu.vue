<script setup lang="ts">
import type { SalesSlip } from '@/views/types'

interface Props {
    item: SalesSlip,
}

const props = defineProps<Props>()
const salesslip = <any>(inject('salesslip'))
const cancelTran = <any>(inject('cancelTran'))
const router = useRouter()

const complaint = () => {
    const params = {
        pg_id: props.item.pg_id,
        mcht_id: props.item.mcht_id,
        appr_dt: props.item.trx_dt?.toString(),
        appr_num: props.item.appr_num,
        issuer: props.item.issuer,
        cust_nm: props.item.buyer_name,
        phone_num: props.item.buyer_phone,
        tid: props.item.tid,
    }
    router.push({
        path: '/complaints/create',
        query: params,
    })
}
</script>
<template>
    <VBtn icon size="x-small" color="default" variant="text">
        <VIcon size="22" icon="tabler-dots-vertical" />
        <VMenu activator="parent">
            <VList>
                <VListItem value="saleslip" @click="salesslip.show(props.item)">
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
                <VListItem value="complaint" @click="cancelTran.show(props.item)" v-show="props.item.is_cancel == false">
                    <template #prepend>
                        <VIcon size="24" class="me-3" icon="tabler:device-tablet-cancel" />
                    </template>
                    <VListItemTitle>취소매출생성</VListItemTitle>
                </VListItem>
            </VList>
        </VMenu>
    </VBtn>
</template>
