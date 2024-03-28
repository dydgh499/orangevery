<script setup lang="ts">
import { useSearchStore } from '@/views/services/mcht-blacklists/useStore'
import BaseIndexView from '@/layouts/lists/BaseIndexView.vue'
import MchtBlacklistCreateDialog from '@/layouts/dialogs/users/MchtBlacklistCreateDialog.vue'
import ExtraMenu from '@/views/services/mcht-blacklists/ExtraMenu.vue'
import { DateFilters } from '@core/enums'

const {
    store,
    head,
    exporter,
    metas
} = useSearchStore()

provide('store', store)
provide('head', head)
provide('exporter', exporter)

const mchtBlackListDlg = ref(null)

provide('mchtBlackListDlg', mchtBlackListDlg)

</script>
<template>
    <div>
        <BaseIndexView placeholder="내용" :metas="metas" :add="false" add_name=""
            :date_filter_type="DateFilters.NOT_USE">
            <template #index_extra_field>
                <VSelect :menu-props="{ maxHeight: 400 }" v-model="store.params.page_size" density="compact"
                    variant="outlined" :items="[10, 20, 30, 50, 100, 200]" label="표시 개수" id="page-size-filter" eager
                    @update:modelValue="store.updateQueryString({ page_size: store.params.page_size })" />
                    
                    <VBtn prepend-icon="arcticons:callsblacklist" @click="mchtBlackListDlg.show({id:0})" size="small">
                        블랙리스트 추가
                    </VBtn>
            </template>
            <template #headers>
                <tr>
                    <template v-for="(header, key, index) in head.headers" :key="index" v-show="header.visible">
                        <th :class="key == 'block_reason' ? 'list-square reason' : 'list-square'">
                            {{ header.ko }}
                        </th>
                    </template>
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
                                <span v-if="_key == 'extra_col'">
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
        <MchtBlacklistCreateDialog ref="mchtBlackListDlg" />
    </div>
</template>
