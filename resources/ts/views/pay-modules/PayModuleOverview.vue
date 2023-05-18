<script lang="ts" setup>
import axios from '@axios';
import type { PayModule } from '@/views/types'
import PayModuleCard from '@/views/pay-modules/PayModuleCard.vue';
import { SearchParams } from '@/views/types';
import { resolveDynamicComponent } from 'vue';

interface Props {
    id: number,
}
const props = defineProps<Props>();

const pgs = [
    {id:1, title:'페이투스'}, {id:2, title:'케이원피에스'}, 
    {id:3, title:'에이닐'}, {id:4, title:'웰컴페이먼츠'}, 
    {id:5, title:'헥토파이넨셜'}, {id:6, title:'루멘페이먼츠'}, 
    {id:7, title:'페이레터'}, {id:8, title:'홀빅'}, 
    {id:9, title:'코페이'}, {id:10, title:'코리아결제시스템'}, 
    {id:11, title:'더페이원'}, {id:12, title:'이지피쥐'}, 
]
const pg_secs = [
    { id: 1, title: '영세' }, { id: 2, title: '중소' },
]
const pay_conds = [
    { id: 1, title: 'D+1' }, { id: 2, title: 'D+2' },
]
const comm_calcs = [
    {}
]
const pay_modules = reactive<PayModule[]>([]);
const count = ref<number>(0);

onMounted(async () => {
    let params = <SearchParams>({
        page: 1,
        page_size: 10000,
        search: '',
        s_dt: getCurrentInstance().appContext.config.globalProperties.$formatDate(new Date(2000, 1, 1)),
        e_dt: getCurrentInstance().appContext.config.globalProperties.$formatDate(new Date(2999, 1, 1))
    })
    params['mcht_id'] = props.id;
    axios.get('/api/v1/manager/pay-modules', { params: params })
        .then(r => {
            Object.assign(pay_modules, r.data.content)            
        })
        .catch(e => {

        })
    axios.get('/api/v1/manager/pay-gateways/detail')
        .then(r => {

        })
        .catch(e => {

        })
})
function getNewPaymodule<PayModule>() {
    const newPayModule = reactive<PayModule>({
        id:0,
        is_old_auth:0,
        mcht_id:7,
        mid:"",
        module_type:5,
        note:"비고란에서 결제모듈명을 입력해주세요.",
        pg_id:1,
        pg_sec_id:1,
        serial_num:"",
    });
    return newPayModule;
}
</script>
<template>
    <PayModuleCard v-for="item in pay_modules" :key="item.id" style="margin-top: 1em;" :item="item" :pg_secs="pg_secs" :pgs="pgs"
        :pay_conds="pay_conds" :comm_calcs="comm_calcs" />
    <PayModuleCard v-for="i in count" :key="i" style="margin-top: 1em;" :item="getNewPaymodule()" :pg_secs="pg_secs" :pgs="pgs"
        :pay_conds="pay_conds" :comm_calcs="comm_calcs" />
    <!-- 👉 submit -->
    <VCard style="margin-top: 1em;">
        <VCol class="d-flex gap-4">
            <VBtn type="submit" style="margin-left: auto;" @click="count++">
                결제모듈 신규추가
                <VIcon end icon="tabler-plus" />
            </VBtn>
        </VCol>
    </VCard>
</template>
