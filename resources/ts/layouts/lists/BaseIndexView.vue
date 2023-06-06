<script setup lang="ts">
import SearchFilterDialog from '@/layouts/dialogs/SearchFilterDialog.vue';
import BaseIndexChart from '@/layouts/lists/BaseIndexChart.vue';
import BaseIndexFilter from '@/layouts/lists/BaseIndexFilter.vue';

interface Props {
    placeholder: string,
    metas: any[],
    add: boolean,
    add_name: string,
}
const props = defineProps<Props>();

const store = <any>(inject('store'))
const filter = ref(null)

onMounted(() => {
    store.filter = filter.value
    watchEffect(() => {
        store.setTable()
    })
    watch(() => store.params.search, (newSearch, oldSearch) => {
    if (newSearch !== oldSearch) {
        store.setTable();
    }
    }, { deep: false });
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
                    <BaseIndexFilter :placeholder="props.placeholder" :add="props.add" :add_name="props.add_name">
                    </BaseIndexFilter>
                    <VDivider />
                    <VTable class="text-no-wrap">
                        <!-- 👉 table head -->
                        <thead>
                            <tr>
                                <slot name="header"></slot>
                            </tr>
                        </thead>
                        <!-- 👉 table body -->
                        <tbody>
                            <slot name="body"></slot>
                        </tbody>
                        <!-- 👉 table footer  -->
                        <tfoot v-show="!Boolean(store.items.length || false)">
                            <tr>
                                <td :colspan="Object.keys(store.headers).length">
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
        <SearchFilterDialog ref="filter" :headers="store.headers" />
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

th,
td {
  text-align: center !important;
}
</style>
