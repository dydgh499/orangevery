<script lang="ts" setup>
import type { PayModule, Merchandise } from '@/views/types'
import PayModuleDialog from '@/layouts/dialogs/pay-modules/PayModuleDialog.vue'
import { module_types } from '@/views/merchandises/pay-modules/useStore'
import { useStore } from '@/views/services/pay-gateways/useStore'

import { getAllPayModules } from '@/views/merchandises/pay-modules/useStore'
import { useRequestStore } from '@/views/request'
import { getUserLevel, isAbleModiy } from '@axios'
import corp from '@corp'

interface Props {
    item: Merchandise,
}

const item_per_page = ref(10)
const page = ref(1)
const sortBy = ref()
const orderBy = ref()
const search = ref('')
const headers = [
    { title: 'No.', key: 'id' },
    { title: '별칭', key: 'note' },
    { title: '결제모듈', key: 'module_type' },
    { title: 'PG사', key: 'pg_id' },
    { title: '구간', key: 'ps_id' },
    { title: 'MID', key: 'mid' },
    { title: 'TID', key: 'tid' },
    { title: '생성시간', key: 'created_at' },
    { title: '업데이트시간', key: 'updated_at' },
]

const payModuleDlg = ref()
const props = defineProps<Props>()
const { setNullRemove } = useRequestStore()
const { pgs, pss, settle_types } = useStore()
const pay_modules = reactive<PayModule[]>([])

// Update data table options
const updateOptions = (options: any) => {
    page.value = options.page
    sortBy.value = options.sortBy[0]?.key
    orderBy.value = options.sortBy[0]?.order
}

const getModuleTypeColor = (module_type: number) => {
    if(module_type === 0)
        return 'default'
    else if(module_type === 1)
        return 'success'
    else if(module_type === 2)
        return 'primary'
    else if(module_type === 4)
        return 'info'
}


const editNewPayModule = async (item: PayModule) => {
    const res = await payModuleDlg.value.show(item)
}

const addNewPayModule = async () => {
    const pay_module = <PayModule>({
        id: 0,
        mcht_id: props.item.id,
        pg_id: null,
        ps_id: null,
        settle_type: null,
        settle_fee: 0,
        terminal_id: null,
        module_type: 0,
        api_key: '',
        sub_key: '',
        p_mid: '',
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
        pay_dupe_limit:0,
        abnormal_trans_limit: Number(corp.pv_options.free.default.abnormal_trans_limit),
        pay_dupe_least: 0,
        pay_year_limit: 0,
        pay_month_limit: 0,
        pay_day_limit: 0,
        pay_single_limit: 0,
        pay_disable_s_tm: null,
        pay_disable_e_tm: null,
        contract_s_dt: null,
        contract_e_dt: null,
        show_pay_view: 1,
        note: '장비',
        filter_issuers: [],
        fin_id: null,
        fin_trx_delay: 15,
        cxl_type: 2,
        use_realtime_deposit: 0,
    })
    const res = await payModuleDlg.value.show(pay_module)
    if(res)
        pay_modules.unshift(<PayModule>(pay_module))
}

if (props.item.id)
    Object.assign(pay_modules, await getAllPayModules(props.item.id))

watchEffect(() => {
    setNullRemove(pay_modules)
})
</script>
<template>
    <div>
        <VCard style="margin-top: 1em;">
            <VCardText class="d-flex flex-wrap py-4 gap-4">
                <div class="app-user-search-filter d-flex flex-wrap gap-4" style="margin-left: auto;">
                    <!-- 👉 Search  -->
                    <div style="inline-size: 15rem;">
                        <AppTextField
                            v-model="search"
                            placeholder="별칭, MID, TID 검색"
                            density="compact"
                            prepend-inner-icon="tabler:search"
                        >
                        <VTooltip activator="parent" location="top">
                            {{ "별칭, MID, TID 검색" }}
                        </VTooltip>
                        </AppTextField>
                    </div>
                        <VBtn v-if="isAbleModiy(0)" prepend-icon="tabler-plus" @click="addNewPayModule">
                            결제모듈 신규추가
                        </VBtn>
                </div>
                <VDivider/>
                <VDataTable v-model:items-per-page="item_per_page" v-model:page="page" :items="pay_modules"
                    :items-length="pay_modules.length" :headers="headers" class="text-no-wrap"
                    :search="search"
                    @update:options="updateOptions">
                    <template v-slot:items="props">
                        <td>{{ props.item.mid }}</td>
                        <td>{{ props.item.tid }}</td>
                        <td>{{ props.item.note }}</td>
                    </template>
                    <template #item.id="{ item }">
                        <b class="edit-link" @click="editNewPayModule(item)">#{{ item.id }}</b>
                    </template>
                    <template #item.note="{ item }">
                        <b class="edit-link" @click="editNewPayModule(item)">{{ item.note }}</b>
                    </template>
                    <template #item.module_type="{ item }">
                        <VChip :color="getModuleTypeColor(item.module_type)">
                            {{ module_types.find(module => module.id === item.module_type)?.title }}
                        </VChip>
                    </template>
                    <template #item.pg_id="{ item }">
                        {{ pgs.find(pg => pg['id'] === item.pg_id)?.pg_name }}
                    </template>
                    <template #item.ps_id="{ item }">
                        {{ pss.find(ps => ps['id'] === item.ps_id)?.name }}
                    </template>
                    <template #item.settle_type="{ item }">
                        {{ settle_types.find(settle_type => settle_type['id'] === item.settle_type)?.name }}
                    </template>
                </VDataTable>
            </VCardText>
            <VDivider/>
        </VCard>
        <PayModuleDialog ref="payModuleDlg" :able_mcht_chanage="false" />
    </div>
</template>
<style scoped>
:deep(.v-table__wrapper) {
  block-size: auto !important;
}
</style>
