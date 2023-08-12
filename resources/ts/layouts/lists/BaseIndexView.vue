<script setup lang="ts">
import SearchFilterDialog from '@/layouts/dialogs/SearchFilterDialog.vue'
import BaseIndexChart from '@/layouts/lists/BaseIndexChart.vue'
import BaseIndexFilter from '@/layouts/lists/BaseIndexFilter.vue'

interface Props {
    placeholder: string,
    metas: any[],
    add: boolean,
    add_name: string,
    is_range_date: boolean | null
}
const props = defineProps<Props>();

const store = <any>(inject('store'))
const head = <any>(inject('head'))
const filter = ref(null)

onMounted(() => {
    head.filter = filter.value
    watchEffect(() => {
        store.setTable()
    })
});


</script>
<template>
    <section>
        <VRow>
            <BaseIndexChart :metas="props.metas">
            </BaseIndexChart>
            <VCol>
                <slot name="filter"></slot>
                <br>
                <VCard>
                    <BaseIndexFilter :placeholder="props.placeholder" :add="props.add" :add_name="props.add_name"
                        :is_range_date="props.is_range_date">
                        <template #index_extra_field>
                            <slot name="index_extra_field"></slot>
                        </template>
                    </BaseIndexFilter>
                    <VDivider />
                    <VTable class="text-no-wrap">
                        <!-- 👉 table head -->
                        <thead>
                            <slot name="headers"></slot>
                        </thead>
                        <!-- 👉 table body -->
                        <tbody>
                            <slot name="body"></slot>
                        </tbody>
                        <!-- 👉 table footer  -->
                        <tfoot v-show="!Boolean(store.items.length)">
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
                            :length="store.pagenation.total_page" />
                    </VCardText>
                </VCard>
            </VCol>
        </VRow>
        <SearchFilterDialog ref="filter" />
    </section>
</template>
