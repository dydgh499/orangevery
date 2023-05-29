<script setup lang="ts">
import { useSearchStore } from '@/views/services/brands/useStore'
import SearchFilterDialog from '@/views/utils/SearchFilterDialog.vue';
import BaseIndexChart from '@/views/utils/BaseIndexChart.vue';
import BaseIndexFilter from '@/views/utils/BaseIndexFilter.vue';

const { store } = useSearchStore()
provide('store', store)

const filter = ref(null)
const metas: any[] = []
const pagenation = computed(() => {
    const firstIndex = store.items.length ? ((store.params.page - 1) * store.params.page_size) + 1 : 0
    const lastIndex = store.items.length + ((store.params.page - 1) * store.params.page_size)
    return `총 ${store.pagenation.total_count}개 항목 중 ${firstIndex} ~ ${lastIndex}개 표시`
})
onMounted(() => {
    store.filter = filter.value
    watchEffect(() => {
        store.setTable()
    })
});
</script>
<template>
    <section>
        <VRow>
            <BaseIndexChart :metas="metas">
            </BaseIndexChart>
            <VCol cols="12">
                <VCard title="검색 옵션">
                    <!-- 👉 Filters -->
                    <VDivider />
                    <BaseIndexFilter :placeholder="`서비스명 검색`" :add="true" :add_name="'서비스'">
                    </BaseIndexFilter>
                    <VDivider />
                    <VTable class="text-no-wrap">
                        <!-- 👉 table head -->
                        <thead>
                            <tr>
                                <th v-for="(header, index) in store.headers" :key="index" scope="col"
                                    v-show="!header.hidden">
                                    {{ header.ko }}
                                </th>
                                <th v-for="(header, index) in store.extra_headers" :key="index" scope="col"
                                v-show="!header.hidden">
                                    {{ header.ko }}
                                </th>
                            </tr>
                        </thead>
                        <!-- 👉 table body -->
                        <tbody>
                            <tr v-for="(user, index) in store.items" :key="index" style="height: 3.75rem;">
                                <td v-for="(header, index) in store.headers" :key="index" scope="col" v-show="!header.hidden">
                                    <span v-if="header.key == 'company_nm' || header.key == 'dns'" class="edit-link" @click="store.edit(user.id)">
                                        {{ store.findValueByKey(user, header.key) }}
                                    </span>
                                    <img v-else-if="header.key == 'logo_img'" :src="user.logo_img" style="max-height: 60px; padding: 0.3em;"/>
                                    <div v-else-if="header.key == 'main_color'" :style="`width: 90%; height: 50%;background:`+user.theme_css.main_color"></div>
                                    <span v-else>{{ store.findValueByKey(user, header.key) }}</span>
                                </td>

                                <td v-for="(header, index) in store.extra_headers" :key="index" scope="col"
                                    v-show="!header.hidden">
                                    <span v-if="header.key == 'deposit_amount'"> {{ user.deposit_amount.toLocaleString() }}</span>
                                    <span v-else>{{ store.findValueByKey(user, header.key) }}</span>
                                </td>
                            </tr>
                        </tbody>

                        <!-- 👉 table footer  -->
                        <tfoot v-show="!Boolean(store.items.length || false)">
                            <tr>
                                <td :colspan="store.headers.length" class="text-center">
                                    검색 결과가 없습니다.
                                </td>
                            </tr>
                        </tfoot>
                    </VTable>
                    <VDivider />
                    <VCardText class="d-flex align-center flex-wrap justify-space-between gap-4 py-3 px-5">
                        <span class="text-sm text-disabled">
                            {{ pagenation }}
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
  font-weight: 900;
}
</style>
