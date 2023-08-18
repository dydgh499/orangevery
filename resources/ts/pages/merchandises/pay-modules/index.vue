<script setup lang="ts">
import { useSearchStore } from '@/views/merchandises/pay-modules/useStore'
import { useStore } from '@/views/services/pay-gateways/useStore'
import { module_types, installments } from '@/views/merchandises/pay-modules/useStore'
import BaseIndexFilterCard from '@/layouts/lists/BaseIndexFilterCard.vue'
import BaseIndexView from '@/layouts/lists/BaseIndexView.vue'
import { user_info } from '@axios'

const { pgs, pss, settle_types } = useStore()
const { store, head, exporter } = useSearchStore()
provide('store', store)
provide('head', head)
provide('exporter', exporter)

const metas = ref([
    {
        icon: 'tabler-user-check',
        color: 'primary',
        title: '금월 추가된 결제모듈',
        stats: '0',
        percentage: 0,
    },
    {
        icon: 'tabler-user-exclamation',
        color: 'error',
        title: '금월 감소한 결제모듈',
        percentage: 0,
        stats: '0',
    },
    {
        icon: 'tabler-user-check',
        color: 'primary',
        title: '금주 추가된 결제모듈',
        percentage: 0,
        stats: '0',
    },
    {
        icon: 'tabler-user-exclamation',
        color: 'error',
        title: '금주 감소한 결제모듈',
        percentage: 0,
        stats: '0',
    },
])
onMounted(() => {
    watchEffect(async() => {
        if(store.getChartProcess() === false) {
            const r = await store.getChartData()
            metas.value[0]['stats'] = r.data.this_month_add.toLocaleString()
            metas.value[1]['stats'] = (r.data.this_month_del * -1).toLocaleString()
            metas.value[2]['stats'] = r.data.this_week_add.toLocaleString()
            metas.value[3]['stats'] = (r.data.this_week_del * -1).toLocaleString()  
            metas.value[0]['percentage'] = store.getPercentage(r.data.this_month_add, r.data.total)
            metas.value[1]['percentage'] = store.getPercentage((r.data.this_month_del * -1), r.data.total)
            metas.value[2]['percentage'] = store.getPercentage(r.data.this_week_add, r.data.total)
            metas.value[3]['percentage'] = store.getPercentage((r.data.this_week_del * -1), r.data.total)            
        }
    })
})
watchEffect(() => {    
    store.setChartProcess()
    store.params.module_type = store.params.module_type
})
</script>
<template>
    <BaseIndexView placeholder="MID, TID, 가맹점 상호 검색" :metas="metas" :add="user_info.level >= 35" add_name="결제모듈" :is_range_date="null">
        <template #filter>
            <BaseIndexFilterCard :pg="true" :ps="true" :settle_type="true" :terminal="true" :cus_filter="true" :sales="true">                
                <template #pg_extra_field>
                    <VCol cols="12" sm="3">
                        <VSelect :menu-props="{ maxHeight: 400 }" v-model="store.params.module_type" :items="[{ id: null, title: '전체' }].concat(module_types)"
                             label="모듈타입 필터" item-title="title" item-value="id" />
                    </VCol>
                </template>
            </BaseIndexFilterCard>
        </template>
        <template #headers>
            <tr>
                <th v-for="(colspan, index) in head.getColspansComputed" :colspan="colspan" :key="index"
                    class='list-square'>
                    <span>
                        {{ head.main_headers[index] }}
                    </span>
                </th>
            </tr>
            <tr>
                <th v-for="(header, key) in head.flat_headers" :key="key" v-show="header.visible" class='list-square'>
                    <span>
                        {{ header.ko }}
                    </span>
                </th>
            </tr>
        </template>
        <template #body>
            <tr v-for="(item, index) in store.getItems" :key="index">
                <template v-for="(_header, _key, _index) in head.headers" :key="_index">
                    <template v-if="head.getDepth(_header, 0) != 1">
                        <td v-for="(__header, __key, __index) in _header" :key="__index" v-show="__header.visible" class='list-square'>
                            <span>
                                {{ item[_key][__key] }}
                            </span>
                        </td>
                    </template>
                    <template v-else>
                        <td v-show="_header.visible" class='list-square'>
                            <span v-if="_key == 'id'" class="edit-link" @click="store.edit(item['id'])">
                                #{{ item[_key] }}
                            </span>
                            <span v-else-if="_key == 'note'" class="edit-link" @click="store.edit(item['id'])">
                                {{ item[_key] }}
                            </span>
                            <span v-else-if="_key == 'module_type'">
                                <VChip :color="store.getSelectIdColor(module_types.find(obj => obj.id === item[_key])?.id)">
                                    {{ module_types.find(module_type => module_type['id'] === item[_key])?.title }}
                                </VChip>
                            </span>
                            <span v-else-if="_key == 'installment'">
                                {{ installments.find(inst => inst['id'] === item[_key])?.title }}
                            </span>
                            <span v-else-if="_key == 'pg_id'">
                                {{ pgs.find(pg => pg['id'] === item[_key])?.pg_name }}
                            </span>
                            <span v-else-if="_key == 'ps_id'">
                                {{ pss.find(ps => ps['id'] === item[_key])?.name }}
                            </span>
                            <span v-else-if="_key == 'settle_type'">
                                {{ settle_types.find(settle_type => settle_type['id'] === item[_key])?.name }}
                            </span>
                            <span v-else>
                                {{ item[_key] }}
                            </span>
                        </td>
                    </template>
                </template>
            </tr>
        </template>
    </BaseIndexView>
</template>
