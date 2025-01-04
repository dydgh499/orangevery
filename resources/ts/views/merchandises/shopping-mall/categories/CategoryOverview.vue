<script setup lang="ts">
import CategoryEditDialog from '@/layouts/dialogs/shopping-mall/CategoryEditDialog.vue';
import BaseIndexView from '@/layouts/lists/BaseIndexView.vue';
import { selectFunctionCollect } from '@/views/selected';
import { axios, getUserLevel } from '@axios';
import { DateFilters } from '@core/enums';
import { useSearchStore } from './useStore';

const { store, head, exporter, metas } = useSearchStore()
const { selected, all_selected } = selectFunctionCollect(store)
const categoryEditDialog = ref()

provide('store', store)
provide('head', head)
provide('exporter', exporter)


const shopPreview = async (mcht_id: number) => {
    const res = await axios.get(`/api/v1/manager/merchandises/${mcht_id}/shop-code`)
    window.open(window.location.origin + `/shop/${res.data.window_code}`, '_blank', "toolbar=no,scrollbars=no,resizable=no,status=no,menubar=no,width=1000, height=1200")
}

</script>
<template> 
    <div>
        <BaseIndexView placeholder="카테고리명, 가맹점 상호 검색" :metas="metas" :add="false" add_name="카테고리"
            :date_filter_type="DateFilters.NOT_USE">
            <template #filter>
            </template>
            <template #index_extra_field>
                <VBtn prepend-icon="material-symbols:work-history-outline" @click="categoryEditDialog.show({ id: 0 }, true)"
                    size="small" :style="$vuetify.display.smAndDown ? 'margin: 0.25em;' : ''">
                    카테고리 추가
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
                    <th v-for="(header, key) in head.flat_headers" :key="key" v-show="header.visible"
                        class='list-square'>
                        <div class='check-label-container' v-if="key == 'id' && getUserLevel() >= 10">
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
                                <span v-if="_key == 'id'">
                                    <div class='check-label-container'>
                                        <VCheckbox v-if="getUserLevel() >= 10" v-model="selected" :value="item[_key]"
                                            class="check-label" />
                                        <span class="edit-link" @click="categoryEditDialog.show(item, false)">
                                            #{{ item[_key] }}
                                            <VTooltip activator="parent" location="top" transition="scale-transition" v-if="$vuetify.display.smAndDown === false">
                                                상세보기
                                            </VTooltip>
                                        </span>
                                    </div>
                                </span>
                                <span v-else-if="_key === 'preview'">
                                    <VBtn type="button" size="small" color="default" variant="tonal" @click="shopPreview(item['mcht_id'])">
                                        쇼핑몰 미리보기
                                        <VIcon end icon="tabler:shopping-cart" />
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
        <CategoryEditDialog ref="categoryEditDialog"/>
    </div>
</template>
