<script setup lang="ts">
import BaseIndexView from '@/layouts/lists/BaseIndexView.vue'
import { complaint_types, useSearchStore } from '@/views/complaints/useStore'
import { useStore } from '@/views/services/pay-gateways/useStore'
import { getUserLevel } from '@axios'
import { DateFilters } from '@core/enums'

const { store, head, exporter } = useSearchStore()
const { pgs } = useStore()

provide('store', store)
provide('head', head)
provide('exporter', exporter)

</script>
<template>
    <BaseIndexView placeholder="가맹점 상호, 고객명, 승인번호, 거래번호 검색" :metas="[]" :add="getUserLevel() >= 35 ? true : false" add_name="민원" :date_filter_type="DateFilters.SETTLE_RANGE">
        <template #index_extra_field>
            <VSelect :menu-props="{ maxHeight: 400 }" v-model="store.params.history_type" density="compact" variant="outlined" item-title="title" item-value="id"
                :items="[{ id: null, title: '전체' }].concat(complaint_types)" label="민원 상태" eager  @update:modelValue="store.updateQueryString({complaint_type: store.params.complaint_type})" 
                :style="$vuetify.display.smAndDown ? 'margin: 0.5em;' : ''"/>
            <VSelect :menu-props="{ maxHeight: 400 }" v-model="store.params.page_size" density="compact" variant="outlined"
                :items="[10, 20, 30, 50, 100, 200]" label="조회 개수" id="page-size-filter" eager @update:modelValue="store.updateQueryString({page_size: store.params.page_size})"
                :style="$vuetify.display.smAndDown ? 'margin: 0.5em;' : ''"/>
        </template>
        <template #headers>
            <tr>
                <th class='list-square' v-for="(header, key) in head.flat_headers" :key="key" v-show="header.visible">
                    <span>
                        {{ header.ko }}
                    </span>
                </th>
            </tr>
        </template>
        <template #body>
            <tr v-for="(item, index) in store.getItems" :key="index">
                <template v-for="(_header, _key, _index) in head.headers" :key="_index">
                        <td v-show="_header.visible" class='list-square'>
                            <span v-if="_key == `id`" class="edit-link" @click="store.edit(item['id'])">
                                #{{ item[_key] }}
                                <VTooltip activator="parent" location="top" transition="scale-transition" v-if="$vuetify.display.smAndDown === false">
                                    상세보기
                                </VTooltip>
                            </span>
                            <span v-else-if="_key == `type`">
                                <VChip :color="store.getSelectIdColor(complaint_types.find(types => types.id === item[_key])?.id)">
                                    {{ complaint_types.find(types => types.id === item[_key])?.title }}
                                </VChip>
                            </span>
                            <span v-else-if="_key == `is_deposit`">
                                <VChip :color="store.booleanTypeColor(!item[_key])">
                                    {{ item[_key] ? '입금' : '미입금' }}
                                </VChip>
                            </span>
                            <span v-else-if="_key == 'pg_id'">
                                {{ pgs.find(pg => pg['id'] === item[_key])?.pg_name }}
                            </span>
                            <div v-else-if="_key === 'note'" class="content">
                                {{ item[_key] }}
                                <VTooltip activator="parent" location="top" transition="scale-transition">
                                    <span>{{ item[_key] }}</span>
                                </VTooltip>
                            </div>
                            <span v-else-if="_key == 'updated_at'" :class="item[_key] !== item['created_at'] ? 'text-primary' : ''">
                                {{ item[_key] }}
                            </span>     
                            <span v-else>
                                {{ item[_key] }}
                            </span>
                        </td>
                </template>
            </tr>
        </template>
    </BaseIndexView>
</template>
<style scoped>
.content {
  overflow: hidden;
  inline-size: 500px !important;
  text-overflow: ellipsis;
  white-space: nowrap;
  word-break: break-all;
}
</style>
