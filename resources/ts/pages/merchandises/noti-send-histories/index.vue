<script setup lang="ts">
import { useSearchStore } from '@/views/merchandises/noti-send-histories/useStore'
import BaseIndexView from '@/layouts/lists/BaseIndexView.vue'
import ExtraMenu from '@/views/merchandises/noti-send-histories/ExtraMenu.vue'

const { store, head, exporter } = useSearchStore()
provide('store', store)
provide('head', head)
provide('exporter', exporter)

</script>
<template>
    <BaseIndexView placeholder="발송 URL 검색" :metas="[]" :add="false" add_name="가맹점" :is_range_date="true">
        <template #filter>
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
                            <span v-if="_key == 'id'" class="edit-link" @click="store.edit(item['id'])">
                                #{{ item[_key] }}
                            </span>
                            <span v-else-if="_key == `trans_id`" class="edit-link">
                                #{{ item[_key] }}
                            </span>
                            <span v-else-if="_key == `http_code`">
                                <VChip :color="store.booleanTypeColor(item[_key] >= 200 && item[_key] <= 299)">
                                    {{ item[_key] }}
                                </VChip>
                            </span>
                            <span v-else-if="_key == 'extra_col'">
                                <ExtraMenu :item="item['id']"></ExtraMenu>
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
