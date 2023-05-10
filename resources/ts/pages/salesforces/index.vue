<script setup lang="ts">
import type { SalesForceProperties } from '@/@fake-db/types';
import { useSearchStore } from '@/views/searchStore';

// 👉 Store
const store = useSearchStore() // params 변수 가져오기
store.items = <SalesForceProperties[]>([])
const role = ref();
const plan = ref();
const status = ref();
const router = useRouter()
// 👉 search filters
const roles = [
  { title: 'Admin', value: 'admin' },
  { title: 'Author', value: 'author' },
  { title: 'Editor', value: 'editor' },
  { title: 'Maintainer', value: 'maintainer' },
  { title: 'Subscriber', value: 'subscriber' },
]

const plans = [
  { title: 'Basic', value: 'basic' },
  { title: 'Company', value: 'company' },
  { title: 'Enterprise', value: 'enterprise' },
  { title: 'Team', value: 'team' },
]

const statuses = [
  { title: 'Pending', value: 'pending' },
  { title: 'Active', value: 'active' },
  { title: 'Inactive', value: 'inactive' },
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
              <VCol cols="12" sm="2">
                <VSelect
                  v-model="plan"
                  label="하위 총판"
                  :items="plans"
                  clearable
                  clear-icon="tabler-x"
                />                
             </VCol>
              <VCol cols="12" sm="2">
                <VSelect
                  v-model="status"
                  label="하위 대리점"
                  :items="statuses"
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
            <div class="app-user-search-filter d-flex align-center flex-wrap gap-4">
              <!-- 👉 Search  -->
              <div style="width: 13.35rem;">
                <VTextField
                  v-model="store.params.search"
                  placeholder="ID, 상호, 대표자명 검색"
                  density="compact"
                />
              </div>

              <!-- 👉 Export button -->
              <VBtn variant="tonal" color="secondary" prepend-icon="tabler-screen-share">
                엑셀 추출
              </VBtn>
              <!-- 👉 Add user button --> 
              <VBtn prepend-icon="tabler-plus" @click="router.replace('salesforces/create')">
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
                <th scope="col">NO.</th>
                <th scope="col">상위 영업자 ID</th>
                <th scope="col">ID</th>
                <th scope="col">대표자명</th>
                <th scope="col">연락처</th>
                <th scope="col">사업자등록번호</th>
                <th scope="col">주민등록번호</th>
                <th scope="col">업종</th>
                <th scope="col">정산 세율</th>
                <th scope="col">주소</th>
                <th scope="col">은행</th>
                <th scope="col">은행코드</th>
                <th scope="col">계좌번호</th>
                <th scope="col">예금주</th>
                <th scope="col">생성시간</th>
                <th scope="col">업데이트시간</th>
                <th scope="col">수정/삭제</th>
              </tr>
            </thead>
            <!-- 👉 table body -->
            <tbody>
              <tr v-for="user in store.items" :key="user.id" style="height: 3.75rem;">
                <td><span>{{ user.id }}</span></td>
                <td>
                    <span>
                        {{ user.user_name+" / "}}                                        
                    </span>
                </td>
                <td>
                  <span>
                    {{ user.user_name+" / "}}
                </span>
                </td>
                <td>
                  <span>{{ user.mcht_name }}</span>
                </td>
                <td>
                  <span>{{ user.nick_name }}</span>
                </td>
                <td>
                  <span>{{ user.phone_num }}</span>
                </td>
                <td>
                  <span>{{ user.business_num }}</span>
                </td>
                <td>
                  <span>{{ user.resident_num }}</span>
                </td>
                <td>
                  <span>{{ user.sector }}</span>
                </td>
                <td>
                  <span>{{ user.addr }}</span>
                </td>
                <td>
                  <span>{{ user.acct_bank_nm }}</span>
                </td>
                <td>
                  <span>{{ user.acct_bank_cd }}</span>
                </td>
                <!-- 👉 Plan -->
                <td>
                  <span>{{ user.acct_num }}</span>
                </td>
                <!-- 👉 Billing -->
                <td>
                    <span>{{ user.acct_nm }}</span>
                </td>
                <td>
                    <span>{{ user.created_at }}</span>
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
                  colspan="7"
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
