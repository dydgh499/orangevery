<script setup lang="ts">
import HolidayDlg from '@/layouts/dialogs/services/HolidayDlg.vue'
import BaseIndexView from '@/layouts/lists/BaseIndexView.vue'
import ExtraMenu from '@/views/services/holidays/ExtraMenu.vue'
import { rest_types, useSearchStore } from '@/views/services/holidays/useStore'
import { useRequestStore } from '@/views/request'
import { DateFilters } from '@core/enums'

const { 
    store, 
    head, 
    exporter, 
    metas
} = useSearchStore()
const { post } = useRequestStore()

const alert = <any>(inject('alert'))
const snackbar = <any>(inject('snackbar'))

provide('store', store)
provide('head', head)
provide('exporter', exporter)


onMounted(() => {
    snackbar.value.show('매일 21시 ~ 오전6시 사이에는 작업할 수 없지만 예외적으로 작업시간을 부여할 수 있습니다.', 'success')
})
</script>
<template>
    <div>
        <BaseIndexView placeholder="공휴일 명칭" :metas="metas" :add="false" add_name="" :date_filter_type="DateFilters.NOT_USE">
            <template #index_extra_field>
                <VSelect :menu-props="{ maxHeight: 400 }" v-model="store.params.page_size" density="compact" variant="outlined"
                    :items="[10, 20, 30, 50, 100, 200]" label="표시 개수" id="page-size-filter" eager  @update:modelValue="store.updateQueryString({page_size: store.params.page_size})" />
                    <VBtn prepend-icon="material-symbols:holiday-village" @click="holidayDlg.show({id:0})" size="small">
                        공휴일 추가
                    </VBtn>
            </template>
            <template #headers>
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
                            <td v-for="(__header, __key, __index) in _header" :key="__index" v-show="__header.visible"
                                class='list-square'>
                            </td>
                        </template>
                        <template v-else>
                            <td v-show="_header.visible" class='list-square'>
                                <span v-if="_key === 'id'">
                                    #{{ item[_key] }}
                                </span>
                                <span v-else-if="_key === 'rest_type'">
                                    <VChip :color="store.getSelectIdColor(item[_key])">
                                        {{ rest_types.find(obj => obj.id === item[_key]).title }}
                                    </VChip>
                                </span>
                                <span v-else-if="_key === 'extra_col'">
                                    <ExtraMenu :item="item"/>
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
        <HolidayDlg ref="holidayDlg"/>
    </div>
</template>
