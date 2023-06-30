<script lang="ts" setup>
import { axios } from '@axios'
import type { PayModule, Merchandise } from '@/views/types'
import PayModuleCard from '@/views/merchandises/pay-modules/PayModuleCard.vue'

interface Props {
    item: Merchandise,
}
const props = defineProps<Props>();

const pay_modules       = reactive<PayModule[]>([]);
const new_pay_modules   = reactive<PayModule[]>([]);
const snackbar      = <any>(inject('snackbar'))

onMounted(async () => {
    const params = {'mcht_id': props.item.id};
    axios.get('/api/v1/manager/merchandises/pay-modules/all', { params: params })
    .then(r => { Object.assign(pay_modules, r.data.content as PayModule[]) })
    .catch(e => { snackbar.value.show(e.response.data.message, 'error') })
})

function addNewPaymodule() {
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
        ship_out_stat: false,
        is_old_auth: false,
        installment: 0,
        note: '비고'
    })
}
</script>
<template>
    <PayModuleCard v-for="(item, index) in pay_modules" :key="index" style="margin-top: 1em;" :item="item" :able_mcht_chanage="false"/>
    <PayModuleCard v-for="(item, index) in new_pay_modules" :key="index" style="margin-top: 1em;" :item="item" :able_mcht_chanage="false"/>
    <!-- 👉 submit -->
    <VCard style="margin-top: 1em;">
        <VCol class="d-flex gap-4">
            <VBtn type="submit" style="margin-left: auto;" @click="addNewPaymodule">
                결제모듈 신규추가
                <VIcon end icon="tabler-plus" />
            </VBtn>
        </VCol>
    </VCard>
</template>
