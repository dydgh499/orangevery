<script setup lang="ts">
import { useSearchStore } from '@/views/popups/useStore'
import BaseIndexView from '@/layouts/lists/BaseIndexView.vue'
import UserExtraMenu from '@/views/users/UserExtraMenu.vue'
import ImageDialog from '@/layouts/dialogs/ImageDialog.vue'
import { DateFilters } from '@core/enums'

const { store, head, exporter } = useSearchStore()
const imageDialog = ref()

provide('store', store)
provide('head', head)
provide('exporter', exporter)

const showAvatar = (preview: string) => {
    imageDialog.value.show(preview)
}

</script>
<template>
    <div>
        <BaseIndexView placeholder="제목 검색" :metas="[]" :add="true" add_name="팝업" :date_filter_type="DateFilters.NOT_USE">
            <template #filter>
            </template>
            <template #index_extra_field>
                <VSelect :menu-props="{ maxHeight: 400 }" v-model="store.params.page_size" density="compact"
                    variant="outlined" :items="[10, 20, 30, 50, 100, 200]" label="표시 개수" id="page-size-filter" eager
                    @update:modelValue="store.updateQueryString({ page_size: store.params.page_size })" />
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
                        </template>
                        <template v-else>
                            <td v-show="_header.visible" class='list-square'>
                                <span v-if="_key == `id`" class="edit-link" @click="store.edit(item['id'])">
                                    #{{ item[_key] }}
                                </span>
                                <span v-else-if="_key == `level`">
                                    <VChip
                                        :color="store.getSelectIdColor(operator_levels.find(obj => obj.id === item[_key])?.id)">
                                        {{ operator_levels.find(obj => obj.id === item[_key])?.title }}
                                    </VChip>
                                </span>
                                <span v-else-if="_key == 'open_range'">
                                    <VChip>
                                        {{ item['open_s_dt'] }}
                                    </VChip>
                                    <span style="margin: 0 0.5em;">~</span>
                                    <VChip>
                                        {{ item['open_e_dt'] }}
                                    </VChip>
                                </span>
                                <span v-else-if="_key == 'profile_img'">
                                    <VAvatar :image="item[_key]" class="me-3 preview"
                                        @click="showAvatar(item['profile_img'])" />
                                </span>
                                <span v-else-if="_key == 'extra_col'">
                                    <UserExtraMenu :id="item['id']" :type="2"></UserExtraMenu>
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
        <ImageDialog ref="imageDialog" :style="`inline-size:20em !important;`" />
    </div>
</template>
