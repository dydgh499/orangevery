<script setup lang="ts">
import type { MerchandiseProperties } from '@/@fake-db/types'
import { useSearchStore } from '@/views/searchStore'


// 👉 Store
const store = useSearchStore()
store.path = 'merchandises';
store.items = [] as MerchandiseProperties[];


store.setHeader('NO.','id')
store.setHeader('상위 영업자 ID', 'group_name')
store.setHeader('ID','user_name')
store.setHeader('상호','mcht_name')
store.setHeader('대표자명','nick_name')
store.setHeader('연락처','phone_num')
store.setHeader('사업자등록번호','resident_num')
store.setHeader('주민등록번호','business_num')
store.setHeader('업종','sector')
store.setHeader('주소','addr')
store.setHeader('은행','acct_bank_nm')
store.setHeader('은행코드','acct_bank_cd')
store.setHeader('예금주','acct_nm')
store.setHeader('계좌번호','acct_num')
store.setHeader('생성시간','created_at')
store.setHeader('업데이트시간','updated_at')

const role = ref()
const router = useRouter()
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
      <VCol
        v-for="meta in userListMeta"
        :key="meta.title"
        cols="12"
        sm="6"
        lg="3"
      >
        <VCard>
          <VCardText class="d-flex justify-space-between">
            <div>
              <span>{{ meta.title }}</span>
              <div class="d-flex align-center gap-2 my-1">
                <h6 class="text-h6">
                  {{ meta.stats }}
                </h6>
                <span :class="meta.percentage > 0 ? 'text-success' : 'text-error'">({{ meta.percentage }}%)</span>
              </div>
              <span>{{ meta.subtitle }}</span>
            </div>

            <VAvatar
              rounded
              variant="tonal"
              :color="meta.color"
              :icon="meta.icon"
            />
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
                <VSelect
                  v-model="role"
                  label="하위 지사"
                  :items="roles"
                  clearable
                  clear-icon="tabler-x"
                />
              </VCol>
            </VRow>
          </VCardText>
          <VDivider />

          <VCardText>
            <VRow>
                <VCol cols="12" sm="2">
                    <VSelect
                        v-model="store.params.page_size"
                        density="compact"
                        variant="outlined"
                        :items="[10, 20, 30, 50]"
                        label="표시 개수"
                    />                
                </VCol>
                <VCol cols="12" sm="2">
                    <AppDateTimePicker
                        v-model="store.params.s_dt"
                        label="검색 시작일"
                    />
                </VCol>
                <VCol cols="12" sm="2">
                    <AppDateTimePicker
                        v-model="store.params.e_dt"
                        label="검색 종료일"
                    />
                </VCol> 
            <VSpacer />
            <div class="d-flex align-center flex-wrap gap-4">
              <!-- 👉 Search  -->
              <div style="width: 13.35rem;">
                <VTextField
                  v-model="store.params.search"
                  placeholder="ID, 상호, 대표자명 검색"
                  density="compact"
                />
              </div>

              <VBtn variant="tonal" color="secondary" prepend-icon="tabler-filter" @click="store.isFilter = true">
                검색 필터
              </VBtn>
              <!-- 👉 Export button -->
              <VBtn variant="tonal" color="secondary" prepend-icon="tabler-screen-share" @click="store.excel()">
                엑셀 추출
              </VBtn>
              <!-- 👉 Add user button --> 
              <VBtn prepend-icon="tabler-plus" @click="router.push('merchandises/create')">
                유저 추가
              </VBtn>
            </div>
            </VRow>
          </VCardText>

          <VDivider />

          <VTable class="text-no-wrap">
            <!-- 👉 table head -->
            <thead>
              <tr>
                <th 
                    v-for="header in store.headers" 
                    :key="header.ko" 
                    scope="col"
                    v-show="!header.hidden"
                >
                {{ header.ko }}
                </th>
                <th scope="col">수정/삭제</th>
              </tr>
            </thead>
            <!-- 👉 table body -->
            <tbody>
              <tr v-for="user in store.items" :key="user.id" style="height: 3.75rem;">
                <td
                    v-for="header in store.headers" 
                    :key="header.key"
                    scope="col"
                    v-show="!header.hidden" 
                >
                    <span>
                        {{ user[header.key] }}
                    </span>
                </td>                
                <!-- 👉 Actions -->
                <td
                  class="text-center"
                  style="width: 5rem;"
                >
                  <VBtn
                    icon
                    size="x-small"
                    color="default"
                    variant="text"
                  >
                    <VIcon
                      size="22"
                      icon="tabler-edit"
                    />
                  </VBtn>

                  <VBtn
                    icon
                    size="x-small"
                    color="default"
                    variant="text"
                  >
                    <VIcon
                      size="22"
                      icon="tabler-trash"
                    />
                  </VBtn>
                </td>
              </tr>
            </tbody>

            <!-- 👉 table footer  -->
            <tfoot v-show="!store.items.length">
              <tr>
                <td
                  colspan="17"
                  class="text-center"
                >
                  가맹점이 존재하지 않습니다.
                </td>
              </tr>
            </tfoot>
          </VTable>

          <VDivider />

          <VCardText class="d-flex align-center flex-wrap justify-space-between gap-4 py-3 px-5">
            <span class="text-sm text-disabled">
              {{ store.paginationData }}
            </span>

            <VPagination
              v-model="store.params.page"
              size="small"
              :total-visible="10"
              :length="store.pagenation.total_page"
            />
          </VCardText>
        </VCard>
      </VCol>
    </VRow>
    <VDialog
        v-model="store.isFilter"
        max-width="600"
    >
        <!-- Dialog close btn -->
        <DialogCloseBtn @click="store.isFilter = !store.isFilter" />
        <!-- Dialog Content -->
        <VCard title="검색 필터">
        <VCardText>
            <VRow>
                <VCol>

                    <VCheckbox
                        v-for="header in store.headers"
                        :key="Object.keys(header)[0]"
                        v-model="header.hidden"
                        :label="header.ko"
                        true-icon="tabler-circle-x"
                        false-icon="tabler-eye-check"
                        color="primary"
                    />
                </VCol>
            </VRow>
        </VCardText>
        </VCard>
    </VDialog>
    <VDialog
        v-model="store.isLoading"
        width="300"
    >
        <VCard
        color="primary"
        width="300"
        >
        <VCardText class="pt-3">
            잠시만 기다려주세요 ...
            <VProgressLinear
            indeterminate
            class="mb-0"
            />
        </VCardText>
        </VCard>
  </VDialog>
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
