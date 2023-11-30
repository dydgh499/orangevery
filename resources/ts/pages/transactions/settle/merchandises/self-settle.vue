
<script setup lang="ts">
import { useSearchStore, realtimeResult, realtimeMessage } from '@/views/transactions/settle/useMerchandiseSelfSettleStore'
import BaseIndexFilterCard from '@/layouts/lists/BaseIndexFilterCard.vue'
import BaseIndexView from '@/layouts/lists/BaseIndexView.vue'
import { DateFilters } from '@core/enums'
import { getUserLevel } from '@axios'

const { store, head, exporter } = useSearchStore()

provide('store', store)
provide('head', head)
provide('exporter', exporter)

</script>
<template>
    <section>
        <BaseIndexView placeholder="가맹점 상호 검색" :metas="[]" :add="false" add_name="실시간 이체 이력" :date_filter_type="DateFilters.SETTLE_RANGE">
            <template #filter>
                <BaseIndexFilterCard :pg="false" :ps="false" :settle_type="false" :terminal="false" :cus_filter="true" :sales="true" v-if="getUserLevel() >= 35">
                </BaseIndexFilterCard>
            </template>
            <template #index_extra_field>
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
                                <span v-if="_key == 'id' || _key == 'trans_id'">
                                    #{{ item[_key] }}
                                </span>
                                <span v-if="_key == 'result_code'">
                                    <VChip :color="store.getSelectIdColor(realtimeResult(item[_key]))">
                                        {{ realtimeMessage(item) }}
                                    </VChip>
                                </span>
                                <span v-else-if="_key == 'withdraw_amount' || _key == 'withdraw_fee'">
                                    {{ item[_key] ? (item[_key] as number).toLocaleString() : 0 }}
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
    </section>
</template>
