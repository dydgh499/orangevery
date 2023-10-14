<script setup lang="ts">
import { useSearchStore } from '@/views/salesforces/useStore'
import BaseIndexFilterCard from '@/layouts/lists/BaseIndexFilterCard.vue'
import BaseIndexView from '@/layouts/lists/BaseIndexView.vue'
import UserExtraMenu from '@/views/users/UserExtraMenu.vue'
import PasswordChangeDialog from '@/layouts/dialogs/PasswordChangeDialog.vue'
import { salesLevels, settleCycles, settleDays, settleTaxTypes } from '@/views/salesforces/useStore'
import { getLevelByIndex } from '@/views/salesforces/useStore'
import { user_info } from '@axios'
import { DateFilters } from '@core/enums'

const { store, head, exporter } = useSearchStore()
const all_sales = salesLevels()
const all_cycles = settleCycles()
const all_days = settleDays()
const tax_types = settleTaxTypes()
const password = ref()

provide('password', password)
provide('store', store)
provide('head', head)
provide('exporter', exporter)

store.params.level = null

const metas = ref([
    {
        icon: 'tabler-user-check',
        color: 'primary',
        title: '금월 추가된 영업점',
        stats: '0',
        percentage: 0,
    },
    {
        icon: 'tabler-user-exclamation',
        color: 'error',
        title: '금월 감소한 영업점',
        percentage: 0,
        stats: '0',
    },
    {
        icon: 'tabler-user-check',
        color: 'primary',
        title: '금주 추가된 영업점',
        percentage: 0,
        stats: '0',
    },
    {
        icon: 'tabler-user-exclamation',
        color: 'error',
        title: '금주 감소한 영업점',
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
        store.params.level = store.params.level
        store.params.settle_cycle = store.params.settle_cycle
    })
</script>
<template>
    <div>
        <BaseIndexView placeholder="영업점 상호 검색" :metas="metas" :add="user_info.level >= 35" add_name="영업점" :date_filter_type="DateFilters.NOT_USE">
            <template #filter>
                <BaseIndexFilterCard :pg="false" :ps="false" :settle_type="false" :terminal="false" :cus_filter="false"
                    :sales="false">
                    <template #sales_extra_field>
                        <VCol cols="12" sm="3">
                            <VSelect :menu-props="{ maxHeight: 400 }" v-model="store.params.level"
                                :items="[{ id: null, title: '전체' }].concat(salesLevels())" :label="`등급 선택`"
                                item-title="title" item-value="id" create />
                        </VCol>
                        <VCol cols="12" sm="3">
                            <VSelect :menu-props="{ maxHeight: 400 }" v-model="store.params.settle_cycle"
                                :items="[{ id: null, title: '전체' }].concat(settleCycles())" :label="`정산주기 선택`"
                                item-title="title" item-value="id" create />
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
                            <td v-for="(__header, __key, __index) in _header" :key="__index" v-show="__header.visible"
                                class='list-square'>
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
                                <span v-else-if="_key == 'user_name'" class="edit-link" @click="store.edit(item['id'])">
                                    {{ item[_key] }}
                                </span>
                                <span v-else-if="_key == 'level'">
                                    <VChip :color="store.getSelectIdColor(getLevelByIndex(item[_key]))">
                                        {{ all_sales.find(obj => obj.id === item[_key])?.title }}
                                    </VChip>
                                </span>
                                <span v-else-if="_key == 'settle_cycle'">
                                    <VChip
                                        :color="store.getSelectIdColor(all_cycles.find(obj => obj.id === item[_key])?.id)">
                                        {{ all_cycles.find(sales => sales.id === item[_key])?.title }}
                                    </VChip>
                                </span>
                                <span v-else-if="_key == 'settle_day'">
                                    {{ all_days.find(sales => sales.id === item[_key])?.title }}
                                </span>
                                <span v-else-if="_key == 'settle_tax_type'">
                                    <VChip
                                        :color="store.getSelectIdColor(tax_types.find(obj => obj.id === item[_key])?.id)">
                                        {{ tax_types.find(sales => sales.id === item[_key])?.title }}
                                    </VChip>
                                </span>
                                <span v-else-if="_key == 'extra_col'">
                                    <UserExtraMenu :id="item['id']" :type="1"></UserExtraMenu>
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
        <PasswordChangeDialog ref="password" />
    </div>
</template>
