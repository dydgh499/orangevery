<script setup lang="ts">
import BillKeyCreateDialog from '@/layouts/dialogs/pay-modules/BillKeyCreateDialog.vue';
import BaseIndexFilterCard from '@/layouts/lists/BaseIndexFilterCard.vue'
import BaseIndexView from '@/layouts/lists/BaseIndexView.vue'
import { useSearchStore } from '@/views/merchandises/pay-modules/bill-keys/useStore'
import { selectFunctionCollect } from '@/views/selected'
import { getUserLevel } from '@axios';
import { DateFilters } from '@core/enums'

const { store, head, exporter } = useSearchStore()
const { selected, all_selected } = selectFunctionCollect(store)
const billKeyCreateDialog = ref()

provide('store', store)
provide('head', head)
provide('exporter', exporter)

</script>
<template>
    <div>
        <BaseIndexView :placeholder="'가맹점 상호 검색'" :metas="[]" :add="false" add_name="빌키"
            :date_filter_type="DateFilters.DATE_RANGE">
            <template #filter>
                <BaseIndexFilterCard :pg="true" :ps="true" :settle_type="true" :terminal="true" :cus_filter="true"
                    :sales="true"/>
            </template>
            <template #index_extra_field>
                <VBtn prepend-icon="tabler-sport-billard" @click="billKeyCreateDialog.show()" size="small" v-if="getUserLevel() >= 35">
                    빌키 추가
                </VBtn>
            </template>
            <template #headers>
                <tr>
                    <th v-for="(header, key) in head.flat_headers" :key="key" v-show="header.visible" class='list-square'>
                        <div class='check-label-container' v-if="key == 'id'">
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
                            <td v-for="(__header, __key, __index) in _header" :key="__index" v-show="__header.visible"
                                class='list-square'>
                                <span>
                                    {{ item[_key][__key] }}
                                </span>
                            </td>
                        </template>
                        <template v-else>
                            <td v-show="_header.visible" class='list-square'>
                                <span v-if="_key === 'id'">
                                    <div
                                        class='check-label-container'>
                                        <VCheckbox v-model="selected" :value="item[_key]" class="check-label" />
                                        <span>#{{ item[_key] }}</span>
                                    </div>
                                </span>
                                <span v-else-if="_key === `extra_col`">                                    
                                    <VBtn prepend-icon="tabler-trash" size="small" type="button" color="error" @click="">
                                        빌키 삭제
                                    </VBtn>
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
        <BillKeyCreateDialog ref="billKeyCreateDialog"/>
    </div>
</template>
