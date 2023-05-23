<script lang="ts" setup>
import { axios } from '@axios';
import type { PayModule, Merchandise } from '@/views/types'
import PayModuleCard from '@/views/merchandises/pay-modules/PayModuleCard.vue';
import { SearchParams } from '@/views/types';
import { useSalesHierarchicalStore } from '@/views/salesforces/useStore'
import corp from '@corp'

interface Props {
    item: Merchandise,
}
const props = defineProps<Props>();

const { flattenUp } = useSalesHierarchicalStore()
const pay_modules       = reactive<PayModule[]>([]);
const new_pay_modules   = reactive<PayModule[]>([]);
const ancestors     = ref<object[]>([]);
const formatDate    = <any>(inject('$formatDate'))
const snackbar      = <any>(inject('snackbar'))

onMounted(async () => {
    let search = <SearchParams>({
        page: 1,
        page_size: 10000,
        search: '',
        s_dt: formatDate(new Date(2000, 1, 1)),
        e_dt: formatDate(new Date(2999, 1, 1))
    })
    const params = Object.assign({}, search, {'mcht_id': props.item.id});
    axios.get('/api/v1/manager/merchandises/pay-modules', { params: params })
    .then(r => {
        Object.assign(pay_modules, r.data.content as PayModule[])
    })
    .catch(e => {
        snackbar.value.show(e.response.data.message, 'error')
    })
})
watchEffect(async () => {
    if(props.item.group_id != 0)
        ancestors.value = await flattenUp(props.item.group_id)
})
function addNewPaymodule() {
    new_pay_modules.push({
        id: 0,
        brand_id: corp.brand_id,
        mcht_id: props.item.id,
        pg_id: 0,
        ps_id: 0,
        terminal_id: 0,
        withdraw_id: 0,
        module_type: 0,
        api_key: '',
        sub_key: '',
        mid: '',
        tid: '',
        serial_num: '',
        comm_pr: 0,
        comm_calc_day: 0,
        comm_calc_id: 0,
        under_sales_amt: 0,
        begin_dt: undefined,
        ship_out_dt: undefined,
        ship_out_stat: false,
        is_old_auth: false,
        use_saleslip_prov: false,
        use_saleslip_sell: false,
        installment: 0,
        note: '비고'
    })
}
</script>
<template>
    <PayModuleCard v-for="item in pay_modules" :key="item.id" style="margin-top: 1em;" :item="item" :ancestors="ancestors"/>
    <PayModuleCard v-for="(item, index) in new_pay_modules" :key="index" style="margin-top: 1em;" :item="item" :ancestors="ancestors"/>
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
