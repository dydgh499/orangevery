<script setup lang="ts">

import { useSearchStore } from '@/views/merchandises/useMchtStore'
import LoadingDialog from '@/views/utils/LoadingDialog.vue';
import SearchFilterDialog from '@/views/utils/SearchFilterDialog.vue';
import AlertDialog from '@/views/utils/AlertDialog.vue';
import Snackbar from '@/views/utils/Snackbar.vue';

// 👉 Store
const {store, pagenation, setHeaders} = useSearchStore()
const alert = ref(null)
const filter = ref(null)
const snackbar = ref(null)
onMounted(() => {
    store.alert = alert.value
    store.filter = filter.value
    store.snackbar = snackbar.value
    setHeaders()
    watchEffect(() => {
        store.setTable()
    }) 
});

const role = ref()
// 👉 search filters
const roles = [
    { title: 'Admin', value: 'admin' },
    { title: 'Author', value: 'author' },
    { title: 'Editor', value: 'editor' },
    { title: 'Maintainer', value: 'maintainer' },
    { title: 'Subscriber', value: 'subscriber' },
]
// 👉 List
const userListMeta = [
    {
        icon: 'tabler-user',
        color: 'primary',
        title: '금월 추가된 가맹점',
        stats: '21,459',
        percentage: +29,
        subtitle: 'Total Users',
    },
    {
        icon: 'tabler-user-plus',
        color: 'error',
        title: '금주 추가된 가맹점',
        stats: '4,567',
        percentage: +18,
        subtitle: 'Last week analytics',
    },
    {
        icon: 'tabler-user-check',
        color: 'success',
        title: '금월 감소한 가맹점',
        stats: '19,860',
        percentage: -14,
        subtitle: 'Last week analytics',
    },
    {
        icon: 'tabler-user-exclamation',
        color: 'warning',
        title: '금주 감소한 가맹점',
        stats: '237',
        percentage: +42,
        subtitle: 'Last week analytics',
    },
]

</script>
<template>
    <section>
        <VRow>
            <VCol v-for="meta in userListMeta" :key="meta.title" cols="12" sm="6" lg="3">
                <VCard>
                    <VCardText class="d-flex justify-space-between">
                        <div>
                            <span>{{ meta.title }}</span>
                            <div class="d-flex align-center gap-2 my-1">
                                <h6 class="text-h6">
                                    {{ meta.stats }}
                                </h6>
                                <span :class="meta.percentage > 0 ? 'text-success' : 'text-error'">({{ meta.percentage
                                }}%)</span>
                            </div>
                            <span>{{ meta.subtitle }}</span>
                        </div>

                        <VAvatar rounded variant="tonal" :color="meta.color" :icon="meta.icon" />
                    </VCardText>
                </VCard>
            </VCol>

            <VCol cols="12">
                <VCard title="검색 옵션">
                    <!-- 👉 Filters -->
                    <VCardText>
                        <VRow>
                            <!-- 👉 Select Plan -->
                            <VCol cols="12" sm="2">
                                <VSelect v-model="role" label="영업자 선택" :items="roles" clearable clear-icon="tabler-x" />
                            </VCol>
                        </VRow>
                    </VCardText>
                    <VDivider />

                    <VCardText>
                        <VRow>
                            <VCol cols="12" sm="2">
                                <VSelect v-model="store.params.page_size" density="compact" variant="outlined"
                                    :items="[10, 20, 30, 50]" label="표시 개수" />
                            </VCol>
                            <VCol cols="12" sm="2">
                                <AppDateTimePicker v-model="store.params.s_dt"
                                    prepend-inner-icon="ic-baseline-calendar-today" label="검색 시작일" />
                            </VCol>
                            <VCol cols="12" sm="2">
                                <AppDateTimePicker v-model="store.params.e_dt"
                                    prepend-inner-icon="ic-baseline-calendar-today" label="검색 종료일" />
                            </VCol>
                            <VSpacer />
                            <div class="d-flex align-center flex-wrap gap-4">
                                <!-- 👉 Search  -->
                                <div style="width: 13.35rem;">
                                    <VTextField v-model="store.params.search" placeholder="ID, 상호, 대표자명 검색"
                                        density="compact" />
                                </div>

                                <VBtn variant="tonal" color="secondary" prepend-icon="tabler-filter"
                                    @click="store.filter.show()">
                                    검색 필터
                                </VBtn>
                                <!-- 👉 Export button -->
                                <VBtn variant="tonal" color="secondary" prepend-icon="tabler-screen-share"
                                    @click="store.excel()">
                                    엑셀 추출
                                </VBtn>
                                <!-- 👉 Add user button -->
                                <VBtn prepend-icon="tabler-plus" @click="store.create()">
                                    유저 추가
                                </VBtn>
                            </div>
                        </VRow>
                    </VCardText>

                    <VDivider />
                    <VTable fixed-header class="text-no-wrap">
                        <!-- 👉 table head -->
                        <thead>
                            <tr>
                                <th v-for="header in store.headers" :key="header.ko" scope="col" v-show="!header.hidden">
                                    {{ header.ko }}
                                </th>
                                <th scope="col">수정/삭제</th>
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
                                <td class="text-center" style="width: 5rem;">
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
                        <tfoot v-show="!store.items.length">
                            <tr>
                                <td :colspan="store.headers.length" class="text-center">
                                    가맹점이 존재하지 않습니다.
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
        <Snackbar ref="snackbar" />
        <AlertDialog ref="alert" />
        <LoadingDialog ref="loading" />
        <SearchFilterDialog ref="filter" :headers="store.headers" />
    </section>
</template>

<style lang="scss">
.app-user-search-filter {
  inline-size: 31.6rem;
}

.text-capitalize {
  text-transform: capitalize;
}

.user-list-name:not(:hover) {
  color: rgba(var(--v-theme-on-background), var(--v-high-emphasis-opacity));
}
</style>

