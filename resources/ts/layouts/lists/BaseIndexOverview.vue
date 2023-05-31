<script setup lang="ts">
import SearchFilterDialog from '@/layouts/dialogs/SearchFilterDialog.vue';
import BaseIndexChart from '@/layouts/lists/BaseIndexChart.vue';
import BaseIndexFilter from '@/layouts/lists/BaseIndexFilter.vue';

interface Props {
    placeholder: string,
    metas: any[],
    add: boolean,
    update: boolean,
}
const props = defineProps<Props>();

const store = <any>(inject('store'))
const setHeaders = <any>(inject('setHeaders'))

const filter = ref(null)
onMounted(() => {
    store.filter = filter.value
    setHeaders()
    watchEffect(() => {
        store.setTable()
    })
});
const pagenation = computed(() => {
    const firstIndex = store.items.length ? ((store.params.page - 1) * store.params.page_size) + 1 : 0
    const lastIndex = store.items.length + ((store.params.page - 1) * store.params.page_size)
    return `총 ${store.pagenation.total_count}개 항목 중 ${firstIndex} ~ ${lastIndex}개 표시`
})
// 👉 Store
</script>
<template>
    <section>
        <VRow>
            <BaseIndexChart :metas="props.metas">
            </BaseIndexChart>
            <VCol cols="12">
                <VCard title="검색 옵션">
                    <!-- 👉 Filters -->
                    <VCardText>
                        <VRow>
                            <slot name="options"></slot>
                        </VRow>
                    </VCardText>
                    <VDivider/>
                    <BaseIndexFilter :placeholder="props.placeholder" :add="props.add">
                    </BaseIndexFilter>
                    <VDivider/>
                    <VTable class="text-no-wrap">
                        <!-- 👉 table head -->
                        <thead>
                            <tr>
                                <th v-for="(header, index) in store.headers" :key="index" scope="col" v-show="!header.hidden">
                                    {{ header.ko }}
                                </th>
                                <slot name="add_headers"></slot>
                                <th scope="col" v-if="props.update">수정/삭제</th>
                            </tr>
                        </thead>
                        <!-- 👉 table body -->
                        <tbody>
                            <tr v-for="user in store.items" :key="user.id" style="height: 3.75rem;">
                                <td v-for="header in store.headers" :key="header.key" scope="col" v-show="!header.hidden">
                                    <span>
                                        {{ user[header.key] }}
                                    </span>
                                </td>
                                <!-- 👉 Actions -->
                                <slot name="add_bodys">

                                </slot>
                                <td class="text-center" style="width: 5rem;" v-if="props.update">
                                    <VBtn icon size="x-small" color="default" variant="text" @click="store.edit(user.id)">
                                        <VIcon size="22" icon="tabler-edit" />
                                    </VBtn>

                                    <VBtn icon size="x-small" color="default" variant="text">
                                        <VIcon size="22" icon="tabler-trash" @click="store.remove(user.id)" />
                                    </VBtn>
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
