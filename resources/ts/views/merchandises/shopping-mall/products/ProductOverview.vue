<script setup lang="ts">
import ProductEditDialog from '@/layouts/dialogs/shopping-mall/ProductEditDialog.vue';
import BaseIndexView from '@/layouts/lists/BaseIndexView.vue';
import Preview from '@/layouts/utils/Preview.vue';
import { pay_window_secure_levels } from '@/views/merchandises/pay-modules/useStore';
import { selectFunctionCollect } from '@/views/selected';
import { getUserLevel } from '@axios';
import { DateFilters } from '@core/enums';
import { useSearchStore } from './useStore';

const { store, head, exporter, metas } = useSearchStore()
const { selected, all_selected } = selectFunctionCollect(store)
const productEditDialog = ref()
const preview_style = `
    border: 1px solid rgb(130, 130, 130);
    border-radius: 0.5em;
    margin: 0.5em;
    width: 150px;
    height: 100px;
`;

provide('store', store)
provide('head', head)
provide('exporter', exporter)
</script>
<template> 
    <div>
        <BaseIndexView placeholder="상품명, 가맹점 상호 검색" :metas="metas" :add="false" add_name="상품"
            :date_filter_type="DateFilters.NOT_USE">
            <template #filter>
            </template>
            <template #index_extra_field>
                <VBtn prepend-icon="tabler:shopping-bag" @click="productEditDialog.show({ id: 0, product_option_groups:[]}, true)"
                    size="small" :style="$vuetify.display.smAndDown ? 'margin: 0.25em;' : ''">
                    상품 추가
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
                                <span v-if="_key === 'id'">
                                    <div class='check-label-container'>
                                        <VCheckbox v-if="getUserLevel() >= 10" v-model="selected" :value="item[_key]"
                                            class="check-label" />
                                        <span class="edit-link" @click="productEditDialog.show(item, false)">
                                            #{{ item[_key] }}
                                            <VTooltip activator="parent" location="top" transition="scale-transition" v-if="$vuetify.display.smAndDown === false">
                                                상세보기
                                            </VTooltip>
                                        </span>
                                    </div>
                                </span>
                                <span v-else-if="_key === 'product_img'" style="display: flex; justify-content: center;">
                                    <Preview :preview="item[_key]" :style="``" :preview-style="preview_style" class="preview" :ext="'png'"/>
                                </span>
                                <span v-else-if="_key === 'product_amount'">
                                    {{ item[_key].toLocaleString() }}
                                </span>
                                <span v-else-if="_key === 'pay_window_secure_level'">
                                    <VChip
                                        :color="store.getSelectIdColor(item[_key])">
                                        {{ pay_window_secure_levels.find(obj => obj.id === item[_key])?.title }}
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
        <ProductEditDialog ref="productEditDialog"/>
    </div>
</template>
