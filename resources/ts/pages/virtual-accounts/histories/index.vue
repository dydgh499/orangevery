<script setup lang="ts">
import WalletDialog from '@/layouts/dialogs/virtual-accounts/WalletDialog.vue';
import WithdrawHistoriesDialog from '@/layouts/dialogs/virtual-accounts/WithdrawHistoriesDialog.vue';
import WithdrawStatusmentDialog from '@/layouts/dialogs/virtual-accounts/WithdrawStatusmentDialog.vue';
import BaseIndexView from '@/layouts/lists/BaseIndexView.vue';
import { StatusColorSetter } from '@/views/searcher';
import ExtraMenu from '@/views/virtual-accounts/histories/ExtraMenu.vue';
import { withdraw_types } from '@/views/virtual-accounts/wallets/useStore';

import {
    depositStatusColors,
    depositStatusNames, transTypeColors, transTypeNames, useSearchStore,
    withdrawStatusColors,
    withdrawStatusNames
} from '@/views/virtual-accounts/histories/useStore';
import { getUserLevel } from '@axios';
import { DateFilters } from '@core/enums';

const { store, head, exporter, metas, dataToChart } = useSearchStore()
store.params.level = 10

const walletDlg = ref()
const withdrawHistoriesDialog = ref()
const withdrawStatusmentDialog = ref()

provide('store', store)
provide('head', head)
provide('exporter', exporter)
provide('withdrawHistoriesDialog', withdrawHistoriesDialog)
provide('withdrawStatusmentDialog', withdrawStatusmentDialog)

onMounted(() => {
    watchEffect(async () => {
        await dataToChart()
    })
})
</script>
<template>
    <div>
        <BaseIndexView placeholder="상호, 지갑별칭 검색" :metas="metas" :add="false" add_name=""
            :date_filter_type="DateFilters.DATE_RANGE">
            <template #filter>
            </template>
            <template #index_extra_field>
                <VSelect :menu-props="{ maxHeight: 400 }" 
                    v-model="store.params.page_size" 
                    :items="[10, 20, 30, 50, 100, 200]" 
                    label="조회 개수" 
                    @update:modelValue="store.updateQueryString({page_size: store.params.page_size})"
                    :style="$vuetify.display.smAndDown ? 'margin: 0.5em;' : ''"/>              
                <VSelect
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
                    v-model="store.params.trans_type" 
                    :items="[{ id: null, title: '전체' }, { id: 0, title: '입금' }, { id: 1, title: '출금' }]" 
                    label="조회 타입" 
                    item-title="title"
                    item-value="id" 
                    @update:modelValue="store.updateQueryString({trans_type: store.params.trans_type})"
                    :style="$vuetify.display.smAndDown ? 'margin: 0.5em;' : ''"/>      
                <VSelect :menu-props="{ maxHeight: 400 }" 
                    v-model="store.params.withdraw_status" 
                    :items="[
                        { id: null, title: '전체' }, { id: 0, title: '대기' }, 
                        { id: 1, title: '성공' }, { id: 2, title: '실패' },
                        { id: 3, title: '승인취소' }, { id: 4, title: '예약취소' }
                    ]" 
                    label="출금 상태" 
                    item-title="title"
                    item-value="id" 
                    @update:modelValue="store.updateQueryString({withdraw_status: store.params.withdraw_status})"
                    :style="$vuetify.display.smAndDown ? 'margin: 0.5em;' : ''"/>      
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
                        <div class='check-label-container' v-if="key == 'id' && getUserLevel() >= 35">
                            <span>NO.</span>
                        </div>
                        <span v-else>
                            {{ header.ko }}
                        </span>
                    </th>
                </tr>
            </template>
            <template #body>
                <tr v-for="(item, index) in store.getItems" :key="index">
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
                                <span v-if="_key == `id`">
                                    <div class='check-label-container'>
                                        <b>
                                            #{{ item[_key] }}
                                        </b>
                                    </div>
                                </span>
                                <span v-else-if="_key === 'account_code'">
                                    <VChip color="success">
                                        {{ item[_key] }}
                                    </VChip>
                                </span>
                                <b v-else-if="_key === 'trans_amount'">
                                    {{ item[_key].toLocaleString() }}
                                </b>
                                <span v-else-if="_key === 'trans_type'">
                                    <VChip :color="transTypeColors(item['trans_type'])">
                                        {{ transTypeNames(item[_key]) }}
                                    </VChip>
                                </span>
                                <span v-else-if="_key === 'settle_id'">
                                    <VChip 
                                        v-if="item['trans_type'] === 0"
                                        :color="'success'">
                                        #{{ item[_key] }}
                                    </VChip>
                                </span>
                                <span v-else-if="_key === 'deposit_status'">
                                    <VChip 
                                        v-if="item['trans_type'] === 0"
                                        :color="depositStatusColors(item[_key])">
                                        {{ depositStatusNames(item[_key]) }}
                                    </VChip>
                                </span>
                                <span v-else-if="_key === 'deposit_schedule_time'">
                                    <span 
                                        v-if="item['trans_type'] === 0"
                                        >
                                        {{ item[_key] }}
                                    </span>
                                </span>
                                <span v-else-if="_key === 'withdraw_type'">
                                    <VChip 
                                        v-if="item['trans_type']"
                                        :color="StatusColorSetter().getSelectIdColor(item[_key])">
                                        {{ withdraw_types.find(obj => obj.id === item[_key])?.title }}
                                    </VChip>
                                </span>
                                <span v-else-if="_key === 'withdraw_status'">
                                    <VChip 
                                        v-if="item['trans_type']"
                                        :color="withdrawStatusColors(item[_key])">
                                        {{ withdrawStatusNames(item[_key]) }}
                                    </VChip>
                                </span>
                                <span v-else-if="_key === 'withdraw_fee'">
                                    <span v-if="item['trans_type']">
                                        {{ item[_key] }}
                                    </span>
                                </span>
                                <span v-else-if="_key === 'withdraw_schedule_time'">
                                    <span v-if="item['trans_type']">
                                        {{ item[_key] }}
                                    </span>
                                </span>
                                <span v-else-if="_key == 'withdraw_etc'">
                                    <ExtraMenu 
                                        v-if="item['trans_type']"
                                        :item="item" />
                                </span>
                                <span v-else-if="_key === 'updated_at'" :class="item[_key] !== item['created_at'] ? 'text-primary' : ''">
                                    {{ item[_key] }}
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
        <WalletDialog ref="walletDlg"/>
        <WithdrawHistoriesDialog ref="withdrawHistoriesDialog" />
        <WithdrawStatusmentDialog ref="withdrawStatusmentDialog" />
    </div>
</template>
