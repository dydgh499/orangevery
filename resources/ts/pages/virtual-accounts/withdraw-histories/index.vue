<script setup lang="ts">
import PVWithdrawErrorCodeDialog from '@/layouts/dialogs/virtual-accounts/PVWithdrawErrorCodeDialog.vue'
import BaseIndexView from '@/layouts/lists/BaseIndexView.vue'
import { useStore } from '@/views/services/pay-gateways/useStore'
import type { VirtualAccountWithdraw } from '@/views/types'
import { useSearchStore } from '@/views/virtual-accounts/withdraw-histories/useStore'
import { getUserLevel } from '@axios'
import { DateFilters } from '@core/enums'

const { store, head, exporter } = useSearchStore()
const { finance_vans } = useStore()
const pvErrorCodeDialog = ref()
store.params.level = 10

provide('store', store)
provide('head', head)
provide('exporter', exporter)

const getLogStyle = (item: VirtualAccountWithdraw) => {
    if(item.result_code !== '0000')
        return 'text-error';
    else
        return '';
}
</script>
<template>
    <div>
        <BaseIndexView placeholder="상호, 지갑별칭, 거래번호, 계좌번호 검색" :metas="[]" :add="false" add_name="출금 상세이력" :date_filter_type="DateFilters.DATE_RANGE">
            <template #filter>
            </template>
            <template #index_extra_field>
                    <VSelect :menu-props="{ maxHeight: 400 }" 
                        v-model="store.params.page_size" 
                        density="compact" 
                        variant="outlined"
                        :items="[10, 20, 30, 50, 100, 200]" 
                        label="조회 개수" 
                        @update:modelValue="store.updateQueryString({page_size: store.params.page_size})"
                        :style="$vuetify.display.smAndDown ? 'margin: 0.5em;' : ''"
                    />
                    <VAutocomplete
                        v-if="getUserLevel() >= 13"
                        :menu-props="{ maxHeight: 400 }" 
                        v-model="store.params.level"
                        :items="[{ id: 10, title: '가맹점' }, { id: 13, title: '영업라인' }]" 
                        label="조회 등급"
                        item-title="title"
                        item-value="id" 
                        @update:modelValue="[store.updateQueryString({ level: store.params.level })]"
                        :style="$vuetify.display.smAndDown ? 'margin: 0.5em;' : ''"
                    />
                    <VSelect :menu-props="{ maxHeight: 400 }" 
                        v-model="store.params.result_code" 
                        :items="[
                            { id: null, title: '전체' },
                            { id: '0000', title: '성공' }, { id: 'PV484', title: '승인취소' },
                            { id: '9999', title: '실패' }, { id: '-5', title: '예약취소' }
                        ]" 
                        label="출금 상태" 
                        item-title="title"
                        item-value="id" 
                        @update:modelValue="store.updateQueryString({result_code: store.params.result_code})"
                        :style="$vuetify.display.smAndDown ? 'margin: 0.5em;' : ''"
                    />
                    <VBtn prepend-icon="line-md:emoji-frown-twotone" @click="pvErrorCodeDialog.show()" color="error" size="small"
                        :style="$vuetify.display.smAndDown ? 'margin: 0.25em;' : ''">
                        출금 에러코드 정의
                    </VBtn>
                <table v-if="getUserLevel() >= 35" >
                    <tr v-for="(finance_van, key) in finance_vans.filter(t => t.is_agency_van === 0 && t.use_kakao_auth === 0 && t.use_account_auth === 0)" :key="key" :style="finance_van.balance_status === 0 ? '' : 'color:red'">
                        <th style="text-align: start;">{{ finance_van.nick_name }} 잔액: </th>
                        <td style="text-align: end;">{{ finance_van.balance ? finance_van.balance.toLocaleString() : 0 }} &#8361;</td>
                    </tr>
                </table>
            </template>
            <template #headers>
                <tr>
                    <template v-for="(sub_header, index) in head.getSubHeaderComputed" :key="index">
                        <th :colspan="head.getSubHeaderComputed.length - 1 == index ? sub_header.width + 1 : sub_header.width"
                            class='list-square sub-headers' v-show="sub_header.width">
                            <span>{{ sub_header.ko }}</span>
                        </th>
                    </template>
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
                                <span v-if="_key == 'id'">
                                    #{{ item[_key] }}
                                </span>
                                <span v-else-if="_key === 'account_code'">
                                    <VChip color="success">
                                        {{ item[_key] }}
                                    </VChip>
                                </span>
                                <span v-else-if="_key === 'va_history_id'">
                                    <VChip 
                                        :color="'success'">
                                        #{{ item[_key] }}
                                    </VChip>
                                </span>
                                <b v-else-if="_key == 'trans_amount'">
                                    {{ item[_key] ? (item[_key] as number).toLocaleString() : 0 }}
                                </b>
                                <span v-else>
                                    {{ item[_key] }}
                                </span>
                            </td>
                        </template>
                    </template>
                </tr>
            </template>
        </BaseIndexView>
        <PVWithdrawErrorCodeDialog ref="pvErrorCodeDialog"/>        
    </div>
</template>
