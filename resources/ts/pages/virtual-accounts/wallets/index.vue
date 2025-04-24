<script setup lang="ts">
import WalletDialog from '@/layouts/dialogs/virtual-accounts/WalletDialog.vue';
import BaseIndexFilterCard from '@/layouts/lists/BaseIndexFilterCard.vue';
import BaseIndexView from '@/layouts/lists/BaseIndexView.vue';
import { StatusColorSetter } from '@/views/searcher';
import { fin_trx_delays, withdraw_limit_types, withdraw_types } from '@/views/virtual-accounts/wallets/useStore';

import { useRequestStore } from '@/views/request';
import { selectFunctionCollect } from '@/views/selected';
import { useStore } from '@/views/services/pay-gateways/useStore';
import { VirtualAccount } from '@/views/types';
import { useSearchStore } from '@/views/virtual-accounts/wallets/useStore';
import { getUserLevel } from '@axios';
import { DateFilters } from '@core/enums';

const alert = <any>(inject('alert'))
const snackbar = <any>(inject('snackbar'))

const { post } = useRequestStore()
const { store, head, exporter } = useSearchStore()
const { selected, all_selected } = selectFunctionCollect(store)
const { finance_vans } = useStore()
store.params.level = 10

const walletDlg = ref()
provide('store', store)
provide('head', head)
provide('exporter', exporter)

const allWithdraw = async (item: VirtualAccount) => {
    if(item.balance > 0) {
        if(await alert.value.show(`정말 ${item.account_name} 지갑에서 전액출금 하시겠습니까?<br><b>출금금액:${item.balance.toLocaleString()}원</b><br><br>(출금수수료는 0원으로 출금됩니다)`)) {
            const r = await post('/api/v1/quick-view/withdraws/collect', {
                va_id: item.id,
                withdraw_amount: item.balance,
                fee_apply: 0,
            })
        }
    }
    else
        snackbar.value.show('출금가능금액이 0원 이하입니다.', 'warning')
}

</script>
<template>
    <div>
        <BaseIndexView placeholder="상호, 계좌별칭 검색" :metas="[]" :add="false" add_name=""
            :date_filter_type="DateFilters.NOT_USE">
            <template #filter>
                <BaseIndexFilterCard :pg="false" :ps="false" :settle_type="false" :terminal="false" :cus_filter="false"
                        :sales="true" :page="false">
                </BaseIndexFilterCard>
            </template>
            <template #index_extra_field>
                <VSelect :menu-props="{ maxHeight: 400 }" 
                    v-model="store.params.page_size" 
                    density="compact" 
                    variant="outlined"
                    :items="[10, 20, 30, 50, 100, 200]" 
                    label="조회 개수" 
                    @update:modelValue="store.updateQueryString({page_size: store.params.page_size})"
                    :style="$vuetify.display.smAndDown ? 'margin: 0.5em;' : ''"/>
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
                            <VCheckbox v-model="all_selected" class="check-label" />
                            <span>선택/취소</span>
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
                                        <VCheckbox 
                                            v-if="getUserLevel() >= 35" 
                                            v-model="selected" 
                                            :value="item[_key]"
                                            class="check-label" 
                                        />
                                        <span class="edit-link" @click="walletDlg.show(0 ,[item])">
                                            #{{ item[_key] }}
                                            <VTooltip activator="parent" location="top" transition="scale-transition" v-if="$vuetify.display.smAndDown === false">
                                                상세보기
                                            </VTooltip>
                                        </span>
                                    </div>
                                </span>
                                <span v-else-if="_key === 'account_code'">
                                    <VChip color="success">
                                        {{ item[_key] }}
                                    </VChip>
                                </span>
                                <b v-else-if="_key === 'balance'">
                                    {{ item[_key].toLocaleString() }}
                                </b>
                                <span v-else-if="_key === 'fin_id'">
                                    {{ finance_vans.find(obj => obj.id === item[_key])?.nick_name }}
                                </span>
                                <span v-else-if="_key === 'fin_trx_delay'">
                                    {{ fin_trx_delays.find(obj => obj.id === item[_key])?.title }}
                                </span>
                                <span v-else-if="_key === 'withdraw_type'">
                                    <VChip :color="StatusColorSetter().getSelectIdColor(item[_key])">
                                        {{ withdraw_types.find(obj => obj.id === item[_key])?.title }}
                                    </VChip>
                                </span>
                                <span v-else-if="_key === 'withdraw_limit_type'">
                                    <VChip :color="StatusColorSetter().getSelectIdColor(item[_key] || 0)">
                                        {{ withdraw_limit_types.find(obj => obj.id === item[_key])?.title }}
                                    </VChip>
                                </span>
                                <span v-else-if="_key === 'withdraw_business_limit' || _key === 'withdraw_holiday_limit'">
                                    {{ item[_key] }}만원
                                </span>
                                <span v-else-if="_key === 'updated_at'" :class="item[_key] !== item['created_at'] ? 'text-primary' : ''">
                                    {{ item[_key] }}
                                </span>
                                <span v-else-if="_key === 'all_withdraw'">
                                    <VBtn variant="tonal" size="small" @click="allWithdraw(item)">
                                        전액출금
                                    </VBtn>
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
    </div>
</template>
