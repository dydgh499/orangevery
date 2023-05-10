<script setup lang="ts">
import type { MerchandiseProperties } from '@/@fake-db/types';
import { useSalesforceListStore } from '@/views/salesforces/salesforceMoudleListStore';

// 👉 Store
const userListStore = useSalesforceListStore()
const selectedRole = ref()
const selectedPlan = ref()
const selectedStatus = ref()

const searchQuery = ref<string>('')
const s_dt = ref<string>('')
const e_dt = ref<string>('')

const rowPerPage = ref(10)
const currentPage = ref(1)
const totalPage = ref<number>(1)
const totalCount = ref(0)
const users = ref<MerchandiseProperties[]>([])

watchEffect(() => {
    userListStore.get(
        {
            page: currentPage.value,
            page_size: rowPerPage.value,
            s_dt: s_dt.value,
            e_dt: e_dt.value,
            search: searchQuery.value,
        },
    ).then(r => {
        let l_page      = r.data.total / rowPerPage.value;
        users.value     = r.data.content
        totalCount.value= r.data.total
        totalPage.value = l_page > Math.floor(l_page) ? l_page + 1 : l_page;
    })
    .catch(e => {
        console.error(e.response.data)
    })
})
// 👉 watching current page
watchEffect(() => {
  if (currentPage.value > totalPage.value)
    currentPage.value = totalPage.value
})

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

const status = [
  { title: 'Pending', value: 'pending' },
  { title: 'Active', value: 'active' },
  { title: 'Inactive', value: 'inactive' },
]

const resolveUserRoleVariant = (role: string) => {
  if (role === 'subscriber')
    return { color: 'warning', icon: 'tabler-user' }
  if (role === 'author')
    return { color: 'success', icon: 'tabler-circle-check' }
  if (role === 'maintainer')
    return { color: 'primary', icon: 'tabler-chart-pie-2' }
  if (role === 'editor')
    return { color: 'info', icon: 'tabler-pencil' }
  if (role === 'admin')
    return { color: 'secondary', icon: 'tabler-device-laptop' }

  return { color: 'primary', icon: 'tabler-user' }
}

const resolveUserStatusVariant = (stat: string) => {
  if (stat === 'pending')
    return 'warning'
  if (stat === 'active')
    return 'success'
  if (stat === 'inactive')
    return 'secondary'

  return 'primary'
}

// 👉 Computing pagination data
const paginationData = computed(() => {
  const firstIndex = users.value.length ? ((currentPage.value - 1) * rowPerPage.value) + 1 : 0
  const lastIndex = users.value.length + ((currentPage.value - 1) * rowPerPage.value)
  
  return `총 ${totalCount.value}개 항목 중 ${firstIndex} ~ ${lastIndex}개 표시`
})

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
                  v-model="selectedRole"
                  label="하위 지사"
                  :items="roles"
                  clearable
                  clear-icon="tabler-x"
                />
              </VCol>
              <VCol cols="12" sm="2">
                <VSelect
                  v-model="selectedPlan"
                  label="하위 총판"
                  :items="plans"
                  clearable
                  clear-icon="tabler-x"
                />                
             </VCol>
              <VCol cols="12" sm="2">
                <VSelect
                  v-model="selectedStatus"
                  label="하위 대리점"
                  :items="status"
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
                        v-model="rowPerPage"
                        density="compact"
                        variant="outlined"
                        :items="[10, 20, 30, 50]"
                        label="표시 개수"
                    />                
                </VCol>
                <VCol cols="12" sm="2">
                    <AppDateTimePicker
                        v-model="s_dt"
                        label="검색 시작일"
                    />
                </VCol>
                <VCol cols="12" sm="2">
                    <AppDateTimePicker
                        v-model="e_dt"
                        label="검색 종료일"
                    />
                </VCol> 
            <VSpacer />
            <div class="app-user-search-filter d-flex align-center flex-wrap gap-4">
              <!-- 👉 Search  -->
              <div style="width: 13.35rem;">
                <VTextField
                  v-model="searchQuery"
                  placeholder="ID, 상호, 대표자명 검색"
                  density="compact"
                />
              </div>

              <!-- 👉 Export button -->
              <VBtn variant="tonal" color="secondary" prepend-icon="tabler-screen-share">
                엑셀 추출
              </VBtn>
              <!-- 👉 Add user button --> 
              <VBtn prepend-icon="tabler-plus" @click="router.replace('user/create')">
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
                <th scope="col">상위 ID/수수료율</th>
                <th scope="col">ID/수수료율</th>
                <th scope="col">상호</th>
                <th scope="col">대표자명</th>
                <th scope="col">연락처</th>
                <th scope="col">사업자등록번호</th>
                <th scope="col">주민등록번호</th>
                <th scope="col">업종</th>
                <th scope="col">주소</th>
                <th scope="col">은행</th>
                <th scope="col">은행코드</th>
                <th scope="col">계좌번호</th>
                <th scope="col">예금주</th>
                <th scope="col">생성시간</th>
                <th scope="col">수정/삭제</th>
              </tr>
            </thead>
            <!-- 👉 table body -->
            <tbody>
              <tr v-for="user in users" :key="user.id" style="height: 3.75rem;">
                <td><span>{{ user.id }}</span></td>
                <td>
                    <span>
                        {{ user.user_name+" / "}}                
                        <VChip
                            label
                            :color="resolveUserStatusVariant(user.addr)"
                            size="small"
                            class="text-capitalize"
                        >
                            {{ +user.trans_fee+"%" }}
                        </VChip>
                    </span>
                </td>
                <td>
                  <span>
                    {{ user.user_name+" / "}}                
                    <VChip
                        label
                        :color="resolveUserStatusVariant(user.addr)"
                        size="small"
                        class="text-capitalize"
                    >
                        {{ +user.trans_fee+"%" }}
                    </VChip>
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
            <tfoot v-show="!users.length">
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
              {{ paginationData }}
            </span>

            <VPagination
              v-model="currentPage"
              size="small"
              :total-visible="10"
              :length="totalPage"
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
