<script setup lang="ts">
import { installments, module_types } from '@/views/merchandises/pay-modules/useStore'
import { useSearchStore } from '@/views/transactions/dangers/useStore'
import BaseIndexFilterCard from '@/layouts/lists/BaseIndexFilterCard.vue'
import BaseIndexView from '@/layouts/lists/BaseIndexView.vue'
import { useStore } from '@/views/services/pay-gateways/useStore'
import ExtraMenu from '@/views/transactions/dangers/ExtraMenu.vue'

const { store, head, exporter } = useSearchStore()
const { pgs, pss, terminals, } = useStore()
const { settle_types } = useStore()

provide('store', store)
provide('head', head)
provide('exporter', exporter)

const mcht_settle_type = ref({ id: null, name: '전체' })
watchEffect(() => {    
    store.params.mcht_settle_type = mcht_settle_type.value.id
})
</script>
<template>
    <BaseIndexView placeholder="가맹점 상호, MID, TID, 승인번호 검색" :metas="[]" :add="false" add_name="가맹점" :is_range_date="true">
        <template #filter>
            <BaseIndexFilterCard :pg="true" :ps="true" :settle_type="false" :terminal="true" :cus_filter="true" :sales="true">
                <template #pg_extra_field>
                        <VCol cols="12" sm="3">
                            <VAutocomplete :menu-props="{ maxHeight: 400 }" v-model="mcht_settle_type"
                                :items="[{ id: null, name: '전체' }].concat(settle_types)" label="정산타입 선택" item-title="name" item-value="id"
                            return-object />
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
                        <td v-for="(__header, __key, __index) in _header" :key="__index" v-show="__header.visible" class='list-square'>
                            <span>
                                {{ item[_key][__key] }}
                            </span>
                        </td>
                    </template>
                    <template v-else>
                        <td v-show="_header.visible" class='list-square'>
                            <span v-if="_key == `id`" class="edit-link">
                                #{{ item[_key] }}
                            </span>
                            <span v-else-if="_key == 'module_type'">
                                <VChip
                                    :color="store.getSelectIdColor(module_types.find(obj => obj.id === item[_key])?.id)">
                                    {{ module_types.find(obj => obj.id === item[_key])?.title }}
                                </VChip>
                            </span>        
                            <span v-else-if="_key == 'installment'">
                                {{ installments.find(inst => inst['id'] === item[_key])?.title as string }}
                            </span>
                            <span v-else-if="_key == 'pg_id'">
                                {{ pgs.find(pg => pg['id'] === item[_key])?.pg_name as string }}
                            </span>
                            <span v-else-if="_key == 'ps_id'">
                                {{ pss.find(pg => pg['id'] === item[_key])?.name as string }}
                            </span>
                            <span v-else-if="_key == 'terminal_id'">
                                {{ terminals.find(inst => inst['id'] === item[_key])?.name as string }}
                            </span>
                            <span v-else-if="_key == 'amount'">
                                {{ (item[_key] as number).toLocaleString() }}
                            </span>
                            <span v-else-if="_key == `danger_type`">
                                <VChip :color="store.booleanTypeColor(!item[_key])" >
                                    {{ item[_key] ? '한도초과' : '중복결제' }}
                                </VChip>
                            </span>
                            <span v-else-if="_key == `is_checked`">
                                <VChip :color="store.booleanTypeColor(!item[_key])" >
                                    {{ item[_key] ? '확인' : '미확인' }}
                                </VChip>
                            </span>
                            <span v-else-if="_key == 'extra_col'">
                                <ExtraMenu :item="item"></ExtraMenu>
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
