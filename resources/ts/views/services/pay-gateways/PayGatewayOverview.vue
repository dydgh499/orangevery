<script lang="ts" setup>
import { useRequestStore } from '@/views/request'
import PayGatewayCard from '@/views/services/pay-gateways/PayGatewayCard.vue'
import { useStore } from '@/views/services/pay-gateways/useStore'
import type { PayGateway } from '@/views/types'

const {pgs } = useStore()
const { setNullRemove } = useRequestStore()
const addNewPG = () => {
    pgs.push(<PayGateway>({
        id: 0,
        pg_type: null,
        pg_name: '',
        rep_name: '',
        company_name: '',
        business_num: '',
        phone_num: '',
        addr:'',
        settle_type:1,
    }))
}

watchEffect(() => {
    setNullRemove(pgs)
})
</script>
<template>
    <VCard>
        <VCol class="d-flex gap-4">
            <VBtn type="button" style="margin-left: auto;" @click="addNewPG">
                PG사 신규추가
                <VIcon end icon="tabler-plus" />
            </VBtn>
        </VCol>
    </VCard>
    <PayGatewayCard v-for="pg in pgs" :key="pg.id" style="margin-top: 1em;" :item="pg"/>
</template>
