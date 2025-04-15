<script setup lang="ts">
import { installments, module_types } from '@/views/merchandises/pay-modules/useStore';
import ExtraMenu from '@/views/transactions/ExtraMenu.vue';
import { getDateFormat, settleIdCol } from '@/views/transactions/transacitonsHeader';
import { getRegidentNum } from '@/views/users/useStore';
import {
    withdrawInterface,
    withdrawStatusColors,
    withdrawStatusNames
} from '@/views/virtual-accounts/histories/useStore';
import corp from '@corp';
import { round_types, useStore } from '../services/pay-gateways/useStore';
import { settlementFunctionCollect } from './settle/Settle';
import { notiSendHistoryInterface } from './transactions';

interface Props {
    item: any,
    _key: string | number,
}

const props = defineProps<Props>()  // defineProps으로 인해 props. 접근방식으로 안해도됨

const store = <any>(inject('store'))
const formatTime = <any>(inject('$formatTime'))

const { isSalesCol } = settlementFunctionCollect(store)
const { pgs, pss, settle_types, terminals, cus_filters } = useStore()
const { notiSendResult, notiSendMessage } = notiSendHistoryInterface()
const { isReSettleAble, isCollectWithdraw } = withdrawInterface()

</script>
<template>
    <span v-if="_key == 'module_type'">
        <VChip
            :color="store.getSelectIdColor(module_types.find(obj => obj.id === item[_key])?.id)">
            {{ module_types.find(obj => obj.id === item[_key])?.title }}
        </VChip>
    </span>
    <span v-else-if="_key == 'settle_id'">
        <VChip :color="settleIdCol(item, store.params.level) === null ? 'default' : 'success'">
            {{ settleIdCol(item, store.params.level) === null ? '정산안함' : "#" + settleIdCol(item,
            store.params.level)}}
        </VChip>
    </span>
    <span v-else-if="_key == 'settle_dt'">
        {{ getDateFormat(item[_key]) }}
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
    <span v-else-if="_key == 'round_type'">
        <VChip :color="store.getSelectIdColor(item[_key])">
            {{ round_types.find(obj => obj.id === item['round_type'])?.title }}
        </VChip>
    </span>
    <span v-else-if="_key == 'mcht_settle_type'">
        {{ settle_types.find(settle_type => settle_type['id'] === item[_key])?.name }}
    </span>
    <span v-else-if="_key == 'terminal_id'">
        {{ terminals.find(terminal => terminal['id'] === item[_key])?.name }}
    </span>
    <b v-else-if="_key === 'only_mcht_fee_profit' && corp.pv_options.free.only_mcht_fee_profit">
        <span v-if="item['withdraw_histories'] && store.params.level === 10">
                <span v-if="item['withdraw_histories'].withdraw_status === 1">
                    {{ Number(item['profit'] - item['withdraw_histories'].withdraw_fee).toLocaleString() }}
                </span>
        </span>
    </b>
    <b v-else-if="_key === 'profit'">
        {{ Number(item[_key]).toLocaleString() }}
    </b>
    <span v-else-if="isSalesCol(_key as string)">
        {{ Number(item[_key]).toLocaleString() }}
    </span>
    <span v-else-if="(_key as string).includes('_fee') && (_key as string).includes('_sales')">
        <VChip v-if="item[`sales${(_key as string).replace(/\D/g, '')}_id`] && (corp.pv_options.free.use_fee_detail_view || item[_key])">
            {{ (item[_key] * 100).toFixed(3) }} %
        </VChip>
    </span>
    <span v-else-if="_key.toString().includes('_fee') && _key != 'mcht_settle_fee'">
        <VChip v-if="item[_key]">
            {{ (item[_key] * 100).toFixed(3) }} %
        </VChip>
    </span>
    <span v-else-if="_key == 'resident_num'">
        <span>{{ getRegidentNum(item, true) }}</span>
    </span>
    <span v-else-if="_key == 'custom_id'">
        {{ cus_filters.find(cus => cus.id === item[_key])?.name }}
    </span>
    <span v-else-if="_key == 'noti_send_result'">
        <VChip :color="store.getSelectIdColor(notiSendResult(item))">
            {{ notiSendMessage(item) }}
        </VChip>
    </span>
    <span v-else-if="_key === 'withdraw_status'">
        <VChip :color="withdrawStatusColors(item['withdraw_histories'] ? item['withdraw_histories'].withdraw_status : 0)">
            <span v-if="item['withdraw_histories'] && store.params.level === 10">
                <span v-if="item['withdraw_histories'].withdraw_status === 0">
                    {{ formatTime(new Date(item['withdraw_histories'].withdraw_schedule_time)) }}초 이체예정
                </span>
                <span v-else>
                    {{ withdrawStatusNames(item['withdraw_histories'].withdraw_status) }}
                </span>
            </span>
            <span v-else-if="store.params.level !== 10">
                {{ 'N/A' }}
            </span>
            <span v-else-if="isReSettleAble(props.item)">
                {{ '지갑 정산하기 필요' }}
            </span>
            <span v-else-if="isCollectWithdraw(props.item)">
                {{ '모아서 출금' }}
            </span>            
            <span v-else>
                {{ 'N/A' }}
            </span>
        </VChip>
    </span>
    <span v-else-if="_key == 'extra_col'">
        <ExtraMenu :item="item" />
    </span>
    <span v-else-if="_key == 'updated_at'"
        :class="item[_key] !== item['created_at'] ? 'text-primary' : ''">
        {{ item[_key] }}
    </span>
    <span v-else>
        {{ item[_key] }}
    </span>
</template>
