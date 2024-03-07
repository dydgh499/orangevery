<script setup lang="ts">
import { useSearchStore } from '@/views/merchandises/sub-business-registrations/useStore'
import BaseIndexFilterCard from '@/layouts/lists/BaseIndexFilterCard.vue'
import BaseIndexView from '@/layouts/lists/BaseIndexView.vue'
import { selectFunctionCollect } from '@/views/selected'
import { getUserLevel } from '@axios'
import { DateFilters } from '@core/enums'
import { template } from 'lodash'

const { store, head, exporter, metas } = useSearchStore()
const { selected, all_selected } = selectFunctionCollect(store)

provide('store', store)
provide('head', head)
provide('exporter', exporter)

onMounted(() => {})
</script>
<template>
    <div>
        <BaseIndexView placeholder="가맹점명 검색" :metas="metas" :add="false" add_name="가맹점"
            :date_filter_type="DateFilters.NOT_USE">
            <template #filter>
                <BaseIndexFilterCard :pg="true" :ps="false" :settle_type="false" :terminal="false" :cus_filter="false"
                    :sales="true">
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

