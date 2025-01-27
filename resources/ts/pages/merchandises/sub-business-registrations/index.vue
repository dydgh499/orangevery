<script setup lang="ts">
import { useSearchStore, registration_types } from '@/views/merchandises/sub-business-registrations/useStore'
import { getDifferenceSettlementResultCode, status_codes } from '@/views/transactions/settle-histories/useDifferenceStore'
import BaseIndexFilterCard from '@/layouts/lists/BaseIndexFilterCard.vue'
import BaseIndexView from '@/layouts/lists/BaseIndexView.vue'
import { useStore } from '@/views/services/pay-gateways/useStore'
import { selectFunctionCollect } from '@/views/selected'
import { getUserLevel } from '@axios'
import { DateFilters } from '@core/enums'

const { store, head, exporter, metas } = useSearchStore()
const { selected, all_selected } = selectFunctionCollect(store)
const { pgs } = useStore()

provide('store', store)
provide('head', head)
provide('exporter', exporter)

onMounted(() => {})
</script>
<template>
    <div>
        <BaseIndexView placeholder="가맹점 상호, 사업자번호 검색" :metas="metas" :add="false" add_name="가맹점"
            :date_filter_type="DateFilters.NOT_USE">
            <template #filter>
                <BaseIndexFilterCard :pg="true" :ps="false" :settle_type="false" :terminal="false" :cus_filter="false"
                    :sales="true">
                    <template #sales_extra_field>
                        <VCol cols="6" sm="3">
                            <VSelect :menu-props="{ maxHeight: 400 }" v-model="store.params.status_code"
                                    :items="status_codes" :label="`결과 필터`" item-title="title" item-value="id"
                                    @update:modelValue="store.updateQueryString({ level: store.params.status_code })" />
                        </VCol>
                    </template>
                    <template #pg_extra_field>
                    </template>
                </BaseIndexFilterCard>
            </template>
            <template #index_extra_field>
            </template>
            <template #headers>
                <tr>
                    <th v-for="(header, key) in head.flat_headers" :key="key" v-show="header.visible" class='list-square'>
                        <div class='check-label-container' v-if="key == 'id'">
                            <VCheckbox v-if="getUserLevel() >= 35" v-model="all_selected" class="check-label"/>
                            <span v-if="getUserLevel() >= 35">선택/취소</span>
                            <span v-else>{{ header.ko }}</span>
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
                        <td v-show="_header.visible" class='list-square'>
                            <span v-if="_key == 'id'">
                                <div class='check-label-container'>
                                    <VCheckbox v-if="getUserLevel() >= 35" v-model="selected" :value="item[_key]" class="check-label"/>
                                    <span class="edit-link" @click="">#{{ item[_key] }}</span>
                                </div>
                            </span>
                            <span v-else-if="_key == 'pg_type'">
                                {{ pgs.find(pg => pg['pg_type'] === item[_key])?.pg_name }}
                            </span>
                            <span v-else-if="_key === 'registration_code'">
                                <VChip :color="getDifferenceSettlementResultCode(item['registration_code'])">
                                    {{ item[_key] }}
                                </VChip>                                
                            </span>
                            <span v-else-if="_key === 'registration_type'">
                                <VChip :color="store.getSelectIdColor(item[_key] + 2)">
                                    {{ registration_types.find(obj => obj.id === item[_key]).title }}
                                </VChip>
                            </span>
                            <span v-else>
                                {{ item[_key] }}
                            </span>
                        </td>
                    </template>
                </tr>
            </template>
        </BaseIndexView>
    </div>
</template>

