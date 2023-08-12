<script setup lang="ts">
import { useSearchStore, noti_statuses } from '@/views/merchandises/noti-urls/useStore'
import BaseIndexFilterCard from '@/layouts/lists/BaseIndexFilterCard.vue'
import BaseIndexView from '@/layouts/lists/BaseIndexView.vue'
import { user_info } from '@axios'

const { store, head, exporter } = useSearchStore()

provide('store', store)
provide('head', head)
provide('exporter', exporter)

</script>
<template>
    <div>
        <BaseIndexView placeholder="발송 URL 검색" :metas="[]" :add="user_info.level >= 35" add_name="노티"
            :is_range_date="null">
            <template #filter>
                <BaseIndexFilterCard :pg="false" :ps="false" :settle_type="false" :terminal="false" :cus_filter="true"
                    :sales="true">
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
                            <td v-for="(__header, __key, __index) in _header" :key="__index"
                                v-show="(__header?.visible as boolean)" class='list-square'>
                                <span>
                                    {{ item[_key][__key] }}
                                </span>
                            </td>
                        </template>
                        <template v-else>
                            <td v-show="_header.visible" class='list-square'>
                                <span v-if="_key == `id`" @click="store.edit(item['id'])">
                                    #{{ item[_key] }}
                                </span>
                                <span v-else-if="_key == 'noti_status'">
                                <VChip :color="store.booleanTypeColor(!noti_statuses.find(obj => obj.id === item[_key])?.id)">
                                    {{ noti_statuses.find(module_type => module_type['id'] === item[_key])?.title }}
                                </VChip>
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
    </div>
</template>
