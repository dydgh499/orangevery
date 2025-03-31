<script setup lang="ts">
import PVErrorCodeDialog from '@/layouts/dialogs/transactions/PVErrorCodeDialog.vue'
import BaseIndexFilterCard from '@/layouts/lists/BaseIndexFilterCard.vue'
import BaseIndexView from '@/layouts/lists/BaseIndexView.vue'
import { module_types } from '@/views/merchandises/pay-modules/useStore'
import { useStore } from '@/views/services/pay-gateways/useStore'
import { useSearchStore } from '@/views/transactions/fails/useStore'
import { getUserLevel } from '@axios'
import { DateFilters } from '@core/enums'

const { pgs, pss } = useStore()
const { store, head, exporter } = useSearchStore()
const pvErrorCodeDialog = ref()
provide('store', store)
provide('head', head)
provide('exporter', exporter)

</script>
<template>
    <div>
        <BaseIndexView placeholder="가맹점 상호 검색" :metas="[]" :add="false" add_name="가맹점" :date_filter_type="DateFilters.DATE_RANGE">
            <template #filter>
                <BaseIndexFilterCard :pg="true" :ps="true" :settle_type="true" :terminal="true" :cus_filter="true" :sales="true" />
            </template>
            <template #index_extra_field>
                <VBtn prepend-icon="line-md:emoji-frown-twotone" @click="pvErrorCodeDialog.show()" v-if="getUserLevel() >= 35" color="error" size="small"
                    :style="$vuetify.display.smAndDown ? 'margin: 0.25em;' : ''">
                    결제/취소 에러코드 정의
                </VBtn>
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
                                <span v-if="_key == `id`">
                                    #{{ item[_key] }}
                                </span>
                                <span v-else-if="_key == 'pg_id'">
                                    {{ pgs.find(pg => pg['id'] === item[_key])?.pg_name }}
                                </span>        
                                <span v-else-if="_key == 'ps_id'">
                                    {{ pss.find(ps => ps['id'] === item[_key])?.name }}
                                </span>
                                <span v-else-if="_key == 'module_type'">
                                    <VChip :color="store.getSelectIdColor(module_types.find(obj => obj.id === item[_key])?.id)">
                                        {{ module_types.find(module_type => module_type['id'] === item[_key])?.title }}
                                    </VChip>
                                </span>
                                <span v-else-if="_key == 'amount'">
                                    {{ (item[_key] as number).toLocaleString() }}
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
        <PVErrorCodeDialog ref="pvErrorCodeDialog"/>
    </div>
</template>
