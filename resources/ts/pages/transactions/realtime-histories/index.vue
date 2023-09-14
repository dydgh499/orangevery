<script setup lang="ts">
import { useSearchStore } from '@/views/transactions/realtime-histories/useStore'
import { useStore } from '@/views/services/pay-gateways/useStore'
import { useRequestStore } from '@/views/request'
import BaseIndexFilterCard from '@/layouts/lists/BaseIndexFilterCard.vue'
import BaseIndexView from '@/layouts/lists/BaseIndexView.vue'
import type { RealtimeHistory } from '@/views/types'
import { onMounted } from 'vue'

const { store, head, exporter } = useSearchStore()
const { post } = useRequestStore()
const { finance_vans } = useStore()

provide('store', store)
provide('head', head)
provide('exporter', exporter)


const snackbar = <any>(inject('snackbar'))
const setFianaceVansBalance = async () => {
    const promises = <any>[]
    for (let i = 0; i < finance_vans.length; i++)  {
        promises.push(post('/api/v1/manager/transactions/realtime-histories/get-balance', finance_vans[i]))
    }
    const results = await Promise.all(promises)
    for (let i = 0; i < results.length; i++) {
        if(results[i]['data']['result_cd'] == "0000") {
            finance_vans[i].balance = parseInt(results[i]['data']['data']['WDRW_CAN_AMT'])
        } 
        else {
            finance_vans[i].balance = 0
            const message = finance_vans[i].nick_name+'의 잔고를 불러오는 도중 에러가 발생하였습니다.<br><br>'+results[i]['data']['result_msg']+'('+results[i]['data']['result_cd']+')'
            snackbar.value.show(message, 'error')
        }
    }
}

const getLogStyle = (item: RealtimeHistory) => {
    if(item.result_code === '0000' && item.request_type === 6170)
        return 'color:blue';
    else if(item.result_code !== '0000' && item.request_type === 6170)
        return 'color:red';
    else
        return '';
}
onMounted(() => {
    setFianaceVansBalance()
})
</script>
<template>
    <BaseIndexView placeholder="가맹점 상호, 계좌번호, 승인번호 검색" :metas="[]" :add="false" add_name="실시간 이체 이력" :is_range_date="true">
        <template #filter>
            <BaseIndexFilterCard :pg="true" :ps="true" :settle_type="false" :terminal="true" :cus_filter="true" :sales="true">
                <template #pg_extra_field>
                </template>
            </BaseIndexFilterCard>
        </template>
        <template #index_extra_field>
            <table>
                <tr v-for="(finance_van, key) in finance_vans" :key="key" :style="finance_van.balance_status ? '' : 'color:red'">
                    <th>{{ finance_van.nick_name }} 잔액: </th>
                    <td><span>{{ finance_van.balance ? finance_van.balance.toLocaleString() : 0 }}</span> &#8361;</td>
                </tr>
            </table>
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
                        <td v-show="_header.visible" class='list-square' :class="getLogStyle(item)">
                            <span v-if="_key == 'id' || _key == 'trans_id'">
                                #{{ item[_key] }}
                            </span>
                            <span>
                                {{ item[_key] }}
                            </span>
                        </td>
                    </template>
                </template>
            </tr>
        </template>
    </BaseIndexView>
</template>
