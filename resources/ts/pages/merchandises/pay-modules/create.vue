
<script setup lang="ts">
import PayModuleCard from '@/views/merchandises/pay-modules/PayModuleCard.vue';
import CreateForm from '@/layouts/utils/CreateForm.vue'
import { useUpdateStore } from '@/views/merchandises/pay-modules/useStore'
import { Merchandise, SearchParams } from '@/views/types';
import { axios } from '@axios';

const { path, item } = useUpdateStore()
const tabs = [
    { icon: 'ic-outline-send-to-mobile', title: '결제모듈정보' },
]
const id = ref<number>(0)
const mchts = ref<Merchandise[]>([])

const setMchts = () => {
    let search = <SearchParams><unknown>({
        page: 1,
        page_size: 10000,
        search: '',
        s_dt: '2000-01-01',
        e_dt: '2999-12-31',
    })
    axios.get('/api/v1/manager/merchandises', { params: search })
        .then(r => { Object.assign(mchts.value, r.data.content as Merchandise[]) })
        .catch(e => { console.log(e) })
}
setMchts()
</script>
<template>
    <section>
        <CreateForm :id="id" :path="path" :tabs="tabs" :item="item">
            <template #view>
                <VWindowItem>
                    <PayModuleCard style="margin-top: 1em;" :item="item" :able_mcht_chanage="true" :mchts="mchts" />
                </VWindowItem>
            </template>
        </CreateForm>
    </section>
</template>
