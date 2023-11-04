<script setup lang="ts">
import { useSearchStore, dev_settle_types } from '@/views/services/brands/useStore'
import { useRequestStore } from '@/views/request'
import BaseIndexView from '@/layouts/lists/BaseIndexView.vue'
import { DateFilters } from '@core/enums'
import corp from '@corp'

const { store, head, exporter, boolToText } = useSearchStore()
const { post } = useRequestStore()
provide('store', store)
provide('head', head)
provide('exporter', exporter)

const remain = ref({
    sms_count: 0,
    lms_count: 0,
    mms_count: 0,
    balance: 0,
})
const getbonaejaBalance = async () => {
    const r = await post('/api/v1/bonaejas/balance', { brand_id: corp.id }, false)
    remain.value.balance = r.data.data.TOTAL_DEPOSIT
    remain.value.sms_count = r.data.data.SMS_CNT
    remain.value.lms_count = r.data.data.LMS_CNT
    remain.value.mms_count = r.data.data.MMS_CNT
}

onMounted(async () => {
    await getbonaejaBalance()
})
/*
    금월 입금 받을 합계액
    금월 입금 함계액
    금월 중간 입금 합계액
*/
</script>
<template>
    <BaseIndexView placeholder="서비스명" :metas="[]" :add="true" add_name="서비스" :date_filter_type="DateFilters.NOT_USE">
        <template #index_extra_field>
            <VSelect :menu-props="{ maxHeight: 400 }" v-model="store.params.page_size" density="compact" variant="outlined"
                :items="[10, 20, 30, 50, 100, 200]" label="표시 개수" id="page-size-filter" eager  @update:modelValue="store.updateQueryString({page_size: store.params.page_size})" />
            <div style="display: flex;">
                <table>
                    <tr
                        :style="(corp.pv_options.free.bonaeja.min_balance_limit * 10000) < remain.balance ? '' : 'color:red'">
                        <th>보내자 보유잔액: </th>
                        <td><span>{{ remain.balance.toLocaleString() }}</span> &#8361;</td>
                    </tr>
                </table>
                <table>                    
                    <tr>
                        <th>SMS 발송가능 회수: </th>
                        <td><span>{{ remain.sms_count.toLocaleString() }}</span>건</td>
                    </tr>
                    <tr>
                        <th>LMS 발송가능 회수: </th>
                        <td><span>{{ remain.lms_count.toLocaleString() }}</span> 건</td>
                    </tr>
                    <tr>
                        <th>MMS 발송가능 회수: </th>
                        <td><span>{{ remain.mms_count.toLocaleString() }}</span> 건</td>
                    </tr>
                </table>
            </div>
        </template>
        <template #headers>
            <tr>
                <th v-for="(colspan, index) in head.getColspansComputed" :colspan="colspan" :key="index" class='list-square'
                    style="border-bottom: 0;">
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
                                <VChip :color="store.booleanTypeColor(!item[_key][__key])">
                                    {{ boolToText(item[_key][__key]) }}
                                </VChip>
                            </span>
                        </td>
                    </template>
                    <template v-else>
                        <td v-show="_header.visible" class='list-square'>
                            <span v-if="_key == `id`" class="edit-link" @click="store.edit(item['id'])">
                                #{{ item[_key] }}
                            </span>
                            <span v-else-if="_key == `dns`" class="edit-link" @click="store.edit(item['id'])">
                                {{ item[_key] }}
                            </span>
                            <span v-else-if="_key == `logo_img`" class="edit-link" @click="store.edit(item['id'])">
                                <img :src="item.logo_img" style="max-height: 60px; padding: 0.3em;" />
                            </span>
                            <span v-else-if="_key == `company_name`" class="edit-link" @click="store.edit(item['id'])">
                                {{ item[_key] }}
                            </span>
                            <span v-else-if="_key == `main_color`">
                                <div :style="`width: 90%; height: 50%;background:` + item.theme_css.main_color"></div>
                            </span>
                            <span
                                v-else-if="_key == `deposit_amount` || _key == `extra_deposit_amount` || _key == 'curr_deposit_amount'">
                                {{ item[_key].toLocaleString() }}
                            </span>
                            <span v-else-if="_key == `dev_fee`">
                                {{ item[_key].toLocaleString() }}%
                            </span>
                            <span v-else-if="_key == 'dev_settle_type'">
                                <VChip :color="store.booleanTypeColor(!item[_key])">
                                    {{ dev_settle_types.find(obj => obj.id === item[_key])?.title }}
                                </VChip>
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
