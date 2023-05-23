<script lang="ts" setup>
import type { PayGateway } from '@/views/types'
import PayGatewayCard from '@/views/services/pay-gateways/PayGatewayCard.vue';
import { useStore } from '@/views/services/pay-gateways/useStore'

interface Props {
    brand_id: number,
}
const {pgs, pss, ternimals, pay_conds, cus_filters } = useStore()

const props = defineProps<Props>();
const new_pay_gateways = reactive<PayGateway[]>([]);
const addNewPG = () => {
    new_pay_gateways.push({
        id: 0,
        brand_id: props.brand_id,
        pg_type: null,
        pg_nm: '',
        rep_nm: '',
        company_nm: '',
        business_num: '',
        phone_num: '',
        addr:'',
    })
}

</script>
<template>
    <PayGatewayCard v-for="pg in pgs" :key="pg.id" style="margin-top: 1em;" :item="pg"/>
    <PayGatewayCard v-for="(new_pg, index) in new_pay_gateways" :key="index" style="margin-top: 1em;" :item="new_pg"/>
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
