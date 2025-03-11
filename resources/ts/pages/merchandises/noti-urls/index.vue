<script setup lang="ts">
import BatchDialog from '@/layouts/dialogs/BatchDialog.vue'
import BaseIndexFilterCard from '@/layouts/lists/BaseIndexFilterCard.vue'
import BaseIndexView from '@/layouts/lists/BaseIndexView.vue'

import { noti_statuses, send_types, useSearchStore } from '@/views/merchandises/noti-urls/useStore'
import { selectFunctionCollect } from '@/views/selected'
import { getUserLevel } from '@axios'
import { DateFilters, ItemTypes } from '@core/enums'

const { store, head, exporter } = useSearchStore()
const { selected, all_selected } = selectFunctionCollect(store)
const batchDialog = ref()

const getSendTypeColor = (send_type: number) => {
    if(send_type === 0)
        return 'primary'
    else if(send_type === 1)
        return 'success'
    else
        return 'error'
}

provide('store', store)
provide('head', head)
provide('exporter', exporter)

</script>
<template>
    <div>
        <BaseIndexView placeholder="가맹점 상호, 발송 URL 검색" :metas="[]" :add="getUserLevel() >= 35" add_name="노티"
            :date_filter_type="DateFilters.NOT_USE">
            <template #filter>
                <BaseIndexFilterCard :pg="false" :ps="false" :settle_type="false" :terminal="false" :cus_filter="true"
                    :sales="true"/>
            </template>
            <template #index_extra_field>
                <VBtn prepend-icon="carbon:batch-job" @click="batchDialog.show()" v-if="getUserLevel() >= 35"
                    color="primary" size="small" style="margin: 0.25em;">
                    일괄작업
                </VBtn>
            </template>
            <template #headers>
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
                                        <VCheckbox v-if="getUserLevel() >= 35" v-model="selected" :value="item[_key]"
                                            class="check-label" />
                                            <span class="edit-link" @click="store.edit(item['id'])">
                                                #{{ item[_key] }}
                                                <VTooltip activator="parent" location="top" transition="scale-transition" v-if="$vuetify.display.smAndDown === false">
                                                    상세보기
                                                </VTooltip>
                                            </span>
                                    </div>
                                </span>
                                <span v-else-if="_key == 'noti_status'">
                                    <VChip :color="store.booleanTypeColor(!noti_statuses.find(obj => obj.id === item[_key])?.id)">
                                        {{ noti_statuses.find(module_type => module_type['id'] === item[_key])?.title }}
                                    </VChip>
                                </span>
                                <span v-else-if="_key == 'send_type'">
                                    <VChip :color="getSendTypeColor(item[_key])">
                                        {{ send_types.find(module_type => module_type['id'] === item[_key])?.title }}
                                    </VChip>
                                </span>
                                <span v-else-if="_key == 'send_type'">
                                    <VChip :color="getSendTypeColor(item[_key])">
                                        {{ send_types.find(module_type => module_type['id'] === item[_key])?.title }}
                                    </VChip>
                                </span>
                                <span v-else-if="_key == 'pmod_id'">
                                    <VChip :color="getSendTypeColor(item[_key])" v-if="item[_key] === -1">
                                        전체발송
                                    </VChip>
                                    <span v-else>
                                        {{ item['pmod_note'] }}
                                    </span>
                                </span>
                                <span v-else-if="_key == 'updated_at'" :class="item[_key] !== item['created_at'] ? 'text-primary' : ''">
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
        <BatchDialog ref="batchDialog" :selected_idxs="selected" :item_type="ItemTypes.NotiUrl"
            @update:select_idxs="selected = $event; store.setTable(); store.getChartData()" />
    </div>
</template>
