<script setup lang="ts">
import { useSearchStore } from '@/views/merchandises/useStore'
import BaseIndexFilterCard from '@/layouts/lists/BaseIndexFilterCard.vue'
import BaseIndexView from '@/layouts/lists/BaseIndexView.vue'
import UserExtraMenu from '@/views/users/UserExtraMenu.vue'
import PasswordChangeDialog from '@/layouts/dialogs/PasswordChangeDialog.vue'
import { module_types } from '@/views/merchandises/pay-modules/useStore'
import { user_info } from '@axios'

const { store, head, exporter, getModuleTypes } = useSearchStore()
const password = ref()

provide('password', password)
provide('store', store)
provide('head', head)
provide('exporter', exporter)

const metas = ref([
    {
        icon: 'tabler-user-check',
        color: 'primary',
        title: '금월 추가된 가맹점',
        stats: '0',
        percentage: 0,
    },
    {
        icon: 'tabler-user-exclamation',
        color: 'error',
        title: '금월 감소한 가맹점',
        percentage: 0,
        stats: '0',
    },
    {
        icon: 'tabler-user-check',
        color: 'primary',
        title: '금주 추가된 가맹점',
        percentage: 0,
        stats: '0',
    },
    {
        icon: 'tabler-user-exclamation',
        color: 'error',
        title: '금주 감소한 가맹점',
        percentage: 0,
        stats: '0',
    },
])
const defaultValue = (values: any[]) => {
    return values.length ? values[0] : ''
}
onMounted(() => {
    watchEffect(async () => {
        if (store.params.page == 1) {
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
    store.params.page = 1
    store.params.module_type = store.params.module_type
})
</script>
<template>
    <div>
        <BaseIndexView placeholder="가맹점 상호, 휴대폰번호, 대표자명 검색" :metas="metas" :add="user_info.level >= 35" add_name="가맹점"
            :is_range_date="null">
            <template #filter>
                <BaseIndexFilterCard :pg="true" :ps="true" :pay_cond="true" :terminal="true" :cus_filter="true"
                    :sales="true">
                    <template #extra_right>
                        <VCol cols="12" sm="3">
                            <VSelect :menu-props="{ maxHeight: 400 }" v-model="store.params.module_type"
                                :items="[{ id: null, title: '전체' }].concat(module_types)" label="모듈타입 선택" item-title="title"
                                item-value="id" />
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
                <tr v-for="(item, index) in store.items" :key="index" style="height: 3.75rem;">
                    <template v-for="(_header, _key, _index) in head.headers" :key="_index">
                        <template v-if="head.getDepth(_header, 0) != 1">
                            <td v-for="(__header, __key, __index) in _header" :key="__index"
                                v-show="(__header?.visible as boolean)" class='list-square'>
                                <span>
                                    {{ item[_key][__key] }}
                                </span>
                            </td>
                        </template>
                        <template v-else>
                            <td v-show="_header.visible" class='list-square'>
                                <span v-if="_key == `id`" class="edit-link" @click="store.edit(item['id'])">
                                    #{{ item[_key] }}
                                </span>
                                <span v-else-if="_key == `user_name`" class="edit-link" @click="store.edit(item['id'])">
                                    {{ item[_key] }}
                                </span>
                                <span v-else-if="(_key as string).includes('_fee')">
                                    <VChip>
                                        {{ (item[_key] as number).toFixed(3) }} %
                                    </VChip>
                                </span>
                                <span v-else-if="_key == 'mids'">
                                    <VSelect style="min-width: 10em;" :value="defaultValue(item['mids'])"
                                        :items="item['mids']" :menu-props="{ maxHeight: 400 }"/>
                                </span>
                                <span v-else-if="_key == 'tids'">
                                    <VSelect style="min-width: 10em;" :value="defaultValue(item['tids'])"
                                        :items="item['tids']" :menu-props="{ maxHeight: 400 }"/>
                                </span>
                                <span v-else-if="_key == 'module_types'">
                                    <VSelect style="min-width: 10em;" :value="defaultValue(getModuleTypes(item['module_types']))"
                                        :items="getModuleTypes(item['module_types'])" :menu-props="{ maxHeight: 400 }"/>
                                </span>
                                <span v-else-if="_key == 'enabled'">
                                    <VChip :color="store.booleanTypeColor(!item[_key])">
                                        {{ item[_key] ? 'ON' : 'OFF' }}
                                    </VChip>
                                </span>
                                <span v-else-if="_key == 'extra_col'">
                                    <UserExtraMenu :id="item['id']" :type="0"></UserExtraMenu>
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
</div></template>
