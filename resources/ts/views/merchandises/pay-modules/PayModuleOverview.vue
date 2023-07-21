<script lang="ts" setup>
import type { PayModule, Merchandise } from '@/views/types'
import PayModuleCard from '@/views/merchandises/pay-modules/PayModuleCard.vue'

import { useMchtFilterStore } from '@/views/merchandises/useStore'
import { usePayModFilterStore } from '@/views/merchandises/pay-modules/useStore'

interface Props {
    item: Merchandise,
}
const props = defineProps<Props>();
const new_pay_modules   = reactive<PayModule[]>([]);

const { merchandises, getAllMerchandises } = useMchtFilterStore()
const { pay_modules, getAllPayModules } = usePayModFilterStore()

if(props.item.id)
    getAllPayModules(props.item.id)
else {
    getAllMerchandises()
}
const addNewPaymodule = () => {
    new_pay_modules.push(<PayModule>{
        id: 0,
        mcht_id: props.item.id,
        pg_id: 0,
        ps_id: 0,
        terminal_id: 0,
        settle_type: 0,
        settle_fee: 0,
        module_type: 0,
        api_key: '',
        sub_key: '',
        mid: '',
        tid: '',
        serial_num: '',
        comm_settle_fee: 0,
        comm_settle_type: 0,
        comm_calc_level: 0,
        under_sales_amt: 0,
        begin_dt: null,
        ship_out_dt: null,
        ship_out_stat: null,
        is_old_auth: false,
        installment: 0,
        pay_dupe_limit:0,
        abnormal_trans_limit: 0,
        pay_year_limit: 0,
        pay_month_limit: 0,
        pay_day_limit: 0,
        pay_disable_s_tm: null,
        pay_disable_e_tm: null,
        show_pay_view: true,
        note: '비고',
    })
}
</script>
<template>
    <PayModuleCard v-for="(item, index) in pay_modules" :key="index" style="margin-top: 1em;" :item="item" :able_mcht_chanage="false" :merchandises="merchandises"/>
    <PayModuleCard v-for="(item, index) in new_pay_modules" :key="index" style="margin-top: 1em;" :item="item" :able_mcht_chanage="false" :merchandises="merchandises"/>
    <!-- 👉 submit -->
    <VCard style="margin-top: 1em;">
        <VCol class="d-flex gap-4">
            <VBtn type="button" style="margin-left: auto;" @click="addNewPaymodule">
                결제모듈 신규추가
                <VIcon end icon="tabler-plus" />
            </VBtn>
        </VCol>
    </VCard>
</template>
