<script lang="ts" setup>
import { axios } from '@axios';
import type { PayModule, Merchandise } from '@/views/types'
import PayModuleCard from '@/views/pay-modules/PayModuleCard.vue';
import { SearchParams } from '@/views/types';
import { useSalesHierarchicalStore } from '@/views/salesforces/useSalesStore'

interface Props {
    item: Merchandise,
}
const props = defineProps<Props>();

const { flattenUp } = useSalesHierarchicalStore()
const pay_modules       = reactive<PayModule[]>([]);
const new_pay_modules   = reactive<PayModule[]>([]);
const ancestors = ref<object[]>([]);

onMounted(async () => {
    let params = <SearchParams>({
        page: 1,
        page_size: 10000,
        search: '',
        s_dt: getCurrentInstance().appContext.config.globalProperties.$formatDate(new Date(2000, 1, 1)),
        e_dt: getCurrentInstance().appContext.config.globalProperties.$formatDate(new Date(2999, 1, 1))
    })
    params['mcht_id'] = props.item.id;
    axios.get('/api/v1/manager/pay-modules', { params: params })
    .then(r => {
        Object.assign(pay_modules, r.data.content as PayModule[])
    })
    .catch(e => {

    })
})
watchEffect(async () => {
    if(props.item.group_id != 0)
        ancestors.value = await flattenUp(props.item.group_id)
    console.log(ancestors.value)
})
function addNewPaymodule() {
    new_pay_modules.push({
        id: 0,
        is_old_auth: false,
        mcht_id: 7,
        mid: "",
        module_type: 0,
        note: "비고(명칭)",
        serial_num: "",
    })
}
</script>
<template>
    <PayModuleCard v-for="item in pay_modules" :key="item.id" style="margin-top: 1em;" :item="item" :ancestors="ancestors"/>
    <PayModuleCard v-for="item in new_pay_modules" :key="i" style="margin-top: 1em;" :item="item" :ancestors="ancestors"/>
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
