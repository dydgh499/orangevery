<script lang="ts" setup>
import type { PayModule, Merchandise } from '@/views/types'
import PayModuleCard from '@/views/merchandises/pay-modules/PayModuleCard.vue'
import MidCreateDialog from '@/layouts/dialogs/MidCreateDialog.vue'
import { getAllPayModules } from '@/views/merchandises/pay-modules/useStore'
import { useRequestStore } from '@/views/request'


interface Props {
    item: Merchandise,
}
const midCreateDlg = ref(null)
const props = defineProps<Props>()
const { setNullRemove } = useRequestStore()
const pay_modules = reactive<PayModule[]>([])

const addNewPaymodule = () => {
    pay_modules.push(<PayModule>{
        id: 0,
        mcht_id: props.item.id,
        pg_id: 0,
        ps_id: 0,
        terminal_id: null,
        settle_type: 0,
        settle_fee: 0,
        module_type: 0,
        api_key: '',
        sub_key: '',
        mid: '',
        tid: '',
        serial_num: '',
        comm_settle_fee: 0,
        comm_settle_day: 1,
        comm_settle_type: 0,
        comm_calc_level: 10,
        under_sales_amt: 0,
        under_sales_limit:0,
        begin_dt: null,
        ship_out_dt: null,
        ship_out_stat: 0,
        is_old_auth: 0,
        installment: 0,
        pay_dupe_limit:0,
        abnormal_trans_limit: 0,
        pay_dupe_least: 0,
        pay_year_limit: 0,
        pay_month_limit: 0,
        pay_day_limit: 0,
        pay_single_limit: 0,
        pay_disable_s_tm: null,
        pay_disable_e_tm: null,
        show_pay_view: true,
        note: '장비',
        fin_id: null,
        fin_trx_delay: 15,
        cxl_type: 2,
        use_realtime_deposit: 0,
        p_mid: '',
    })
}

provide('midCreateDlg', midCreateDlg)

if(props.item.id)
    Object.assign(pay_modules, await getAllPayModules(props.item.id))

watchEffect(() => {
    setNullRemove(pay_modules)
})
</script>
<template>
    <div>
        <PayModuleCard v-for="(item, index) in pay_modules" :key="index" style="margin-top: 1em;" :item="item" :able_mcht_chanage="false"/>
        <!-- 👉 submit -->
        <VCard style="margin-top: 1em;">
            <VCol class="d-flex gap-4">
                <VBtn type="button" style="margin-left: auto;" @click="addNewPaymodule">
                    결제모듈 신규추가
                    <VIcon end icon="tabler-plus" />
                </VBtn>
            </VCol>
        </VCard>
        <MidCreateDialog ref="midCreateDlg"/>
    </div>
</template>
