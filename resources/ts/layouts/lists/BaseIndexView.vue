<script setup lang="ts">
import SearchFilterDialog from '@/layouts/dialogs/utils/SearchFilterDialog.vue';
import BaseIndexChart from '@/layouts/lists/BaseIndexChart.vue';
import BaseIndexFilter from '@/layouts/lists/BaseIndexFilter.vue';
import SkeletonBox from '@/layouts/utils/SkeletonBox.vue';

interface Props {
    placeholder: string,
    metas: any[],
    add: boolean,
    add_name: string,
    date_filter_type: number | null,
    sub_search_name?: string,
    sub_search_placeholder?: string,
}
const props = defineProps<Props>()

const store = <any>(inject('store'))
const head = <any>(inject('head'))
const filter = ref(null)

onMounted(() => {
    head.filter = filter.value
    watchEffect(() => {
        store.setTable()
    })
    watchEffect(() => {
        //useDynamicTabStore().updateParams(store.params)
    })
});
</script>
<template>
    <section>
        <VRow>
            <BaseIndexChart :metas="props.metas"/>
            <VCol>
                <slot name="filter"></slot>
                <VCard>
                    <BaseIndexFilter 
                        :placeholder="props.placeholder" 
                        :sub_search_name="props.sub_search_name" 
                        :sub_search_placeholder="props.sub_search_placeholder" 
                        :add="props.add" 
                        :add_name="props.add_name"
                        :date_filter_type="props.date_filter_type"
                        >
                        <template #index_extra_field>
                            <slot name="index_extra_field"></slot>
                        </template>
                    </BaseIndexFilter>
                    <VDivider />
                    <VTable class="text-no-wrap">
                        <!-- 👉 table head -->
                        <thead>
                            <slot name="headers"></slot>
                            <template v-if="store.getSkeleton()">
                                <tr v-for="(item, index) in 15" :key="index">
                                    <template v-for="(_header, _key, _index) in head.headers" :key="_index">
                                        <template v-if="head.getDepth(_header, 0) != 1">
                                            <td v-for="(__header, __key, __index) in _header" :key="__index" v-show="__header.visible"
                                                class='list-square'>
                                                <SkeletonBox/>
                                            </td>
                                        </template>
                                        <template v-else>
                                            <td v-show="_header.visible" class='list-square'>
                                                <span v-if="_key == `id`">
                                                    # <SkeletonBox :width="'2em'"/>
                                                </span>
                                                <SkeletonBox v-else/>
                                            </td>
                                        </template>

                                    </template>
                                </tr>
                            </template>
                        </thead>
                        <!-- 👉 table body -->
                        <tbody>
                            <slot name="body"></slot>
                        </tbody>
                        <!-- 👉 table footer  -->
                        <tfoot v-if="store.pagenation.total_page === 0 && store.getSkeleton() === false">
                            <tr>
                                <td :colspan="Object.keys(head.flat_headers).length" class='list-square' style="border: 0;">
                                    검색 결과가 없습니다.
                                </td>
                            </tr>
                        </tfoot>
                    </VTable>
                    <VDivider />
                    <VCardText class="d-flex align-center flex-wrap justify-space-between gap-4 py-3"
                        style=" padding-right: 0 !important;padding-left: 0 !important;">
                        <span class="text-sm text-disabled" style="padding-left: 10px;">
                            {{ store.pagenationCouputed }}
                        </span>                        
                        <VPagination v-model="store.params.page" size="small" :total-visible="10"
                            :length="store.pagenation.total_page" @update:modelValue="store.updateQueryString({page: store.params.page})" />
                    </VCardText>
                </VCard>
            </VCol>
        </VRow>
        <SearchFilterDialog ref="filter" />
    </section>
</template>
