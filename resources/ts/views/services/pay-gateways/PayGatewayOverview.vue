<script lang="ts" setup>
import type { PayGateway } from '@/views/types'
import PayGatewayCard from '@/views/services/pay-gateways/PayGatewayCard.vue'
import { useStore } from '@/views/services/pay-gateways/useStore'
import { useRequestStore } from '@/views/request'

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
        settle_type:0,        
        rep_mid: '',
        sftp_id: '',
        sftp_password: '',
        use_different_settlement: 0,
    }))
}

watchEffect(() => {
    setNullRemove(pgs)
})
</script>
<template>
    <PayGatewayCard v-for="pg in pgs" :key="pg.id" style="margin-top: 1em;" :item="pg"/>
    <!-- 👉 submit -->
    <VCard style="margin-top: 1em;">
        <VCol class="d-flex gap-4">
            <VBtn type="button" style="margin-left: auto;" @click="addNewPG">
                PG사 신규추가
                <VIcon end icon="tabler-plus" />
            </VBtn>
        </VCol>
    </VCard>
</template>
