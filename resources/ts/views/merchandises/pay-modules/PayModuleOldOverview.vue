
<script setup lang="ts">
import PayModuleCard from '@/views/merchandises/pay-modules/PayModuleCard.vue'
import { getAllPayModules } from '@/views/merchandises/pay-modules/useStore'
import { useRequestStore } from '@/views/request'
import type { Merchandise, PayModule } from '@/views/types'
import { isAbleModiy } from '@axios'
import corp from '@corp'

interface Props {
    item: Merchandise,
}
const props = defineProps<Props>()
const { setNullRemove } = useRequestStore()
const pay_modules = reactive<PayModule[]>([])

const midCreateDlg = ref(null)
provide('midCreateDlg', midCreateDlg)

const addNewPayModule = async () => {
    const pay_module = <PayModule>({
        id: 0,
        mcht_id: props.item.id,
        pg_id: null,
        ps_id: null,
        terminal_id: null,
        settle_type: 0,
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
        under_sales_type: 0,
        under_sales_limit: 0,
        begin_dt: null,
        ship_out_dt: null,
        ship_out_stat: 0,
        is_old_auth: 0,
        installment: Number(corp.pv_options.free.default.installment),
        note: '장비',
        settle_fee: 0,
        pay_dupe_limit: 0,
        abnormal_trans_limit: Number(corp.pv_options.free.default.abnormal_trans_limit),
        pay_year_limit: 0,
        pay_month_limit: 0,
        pay_day_limit: 0,
        pay_single_limit: 0,
        pay_disable_s_tm: '00:00',
        pay_disable_e_tm: '00:00',
        pay_window_secure_level: 0,
        pay_window_extend_hour: 25,
        pay_key: '',
        filter_issuers: [],
        contract_s_dt: null,
        contract_e_dt: null,
        fin_id: null,
        fin_trx_delay: 15,
        cxl_type: 2,
        use_realtime_deposit: 0,
        pay_dupe_least: 0,
        payment_term_min: 1,
        p_mid: '',
        is_different_settlement: 1,
        pay_limit_type: 0,
        withdraw_limit_type: 0,
    })
    pay_modules.unshift(<PayModule>(pay_module))
}

if (props.item.id)
    Object.assign(pay_modules, await getAllPayModules(props.item.id))

watchEffect(() => {
    setNullRemove(pay_modules)
})
</script>
<template>
    <section>
        <VCard style="margin-top: 1em;" v-if="isAbleModiy(0)">
            <VCol class="d-flex gap-4">
                <VBtn type="button" style="margin-left: auto;" @click="addNewPayModule">
                    결제모듈 신규추가
                    <VIcon end icon="tabler-plus" />
                </VBtn>
            </VCol>
        </VCard>
        <VCard style="margin-top: 1em;" v-if="isAbleModiy(0) === false && pay_modules.length === 0">
            <VCol class="d-flex gap-4">
                등록된 결제모듈이 없습니다.
            </VCol>
        </VCard>
        <PayModuleCard v-for="(pay_module, index) in pay_modules" :key="index" 
            :item="pay_module" :able_mcht_chanage="false" style="margin-top: 1em;"/>
    </section>
</template>
