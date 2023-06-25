<script setup lang="ts">
import SearchFilterDialog from '@/layouts/dialogs/SearchFilterDialog.vue';
import BaseIndexChart from '@/layouts/lists/BaseIndexChart.vue';
import BaseIndexFilter from '@/layouts/lists/BaseIndexFilter.vue';

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
                    <BaseIndexFilter :placeholder="props.placeholder" :add="props.add" :add_name="props.add_name" :is_range_date="props.is_range_date">
                    </BaseIndexFilter>
                    <VDivider />
                    <VTable class="text-no-wrap">
                        <!-- 👉 table head -->
                        <thead>
                                <slot name="headers"></slot>
                            <tr>
                                <slot name="content-header"></slot>
                                <slot name="header"></slot>
                            </tr>
                        </thead>
                        <!-- 👉 table body -->
                        <tbody>
                            <slot name="body"></slot>
                        </tbody>
                        <!-- 👉 table footer  -->
                        <tfoot v-show="!Boolean(store.items.length)">
                            <tr>
                                <td :colspan="Object.keys(head.flat_headers).length" style="text-align: center;">
                                    검색 결과가 없습니다.
                                </td>
                            </tr>
                        </tfoot>
                    </VTable>
                    <VDivider />
                    <VCardText class="d-flex align-center flex-wrap justify-space-between gap-4 py-3 px-5">
                        <span class="text-sm text-disabled">
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
<style lang="scss">
.edit-link {
  color: rgb(var(--v-global-theme-primary));
  cursor: pointer;
}

.edit-link:hover {
  font-weight: 900;
}

.list-square {
  padding-block: 0;
  padding-inline: 6px !important;
  text-align: center !important;
}
</style>
