<script setup lang="ts">
import type { PaymentModuleProperties } from '@/@fake-db/types';
import { usePaymentModuleListStore } from '@/views/payment-modules/paymentModuleListStore';

const router = useRouter()

// 👉 Store
const payListStore = usePaymentModuleListStore()
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
const pays = ref<PaymentModuleProperties[]>([])

watchEffect(() => {
    payListStore.get(
        {
            page: currentPage.value,
            page_size: rowPerPage.value,
            s_dt: s_dt.value,
            e_dt: e_dt.value,
            search: searchQuery.value,
        },
    ).then(r => {
        let l_page  = r.data.total / rowPerPage.value;
        pays.value  = r.data.content
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


// 👉 Computing pagination data
const paginationData = computed(() => {
  const firstIndex = pays.value.length ? ((currentPage.value - 1) * rowPerPage.value) + 1 : 0
  const lastIndex = pays.value.length + ((currentPage.value - 1) * rowPerPage.value)
  
  return `총 ${totalCount.value}개 항목 중 ${firstIndex} ~ ${lastIndex}개 표시`
})

// 👉 List
const payListMeta = [
  {
    icon: 'tabler-pay',
    color: 'primary',
    title: '금월 추가된 결제모듈',
    stats: '21,459',
    percentage: +29,
    subtitle: 'Total pays',
  },
  {
    icon: 'tabler-pay-plus',
    color: 'error',
    title: '금주 추가된 결제모듈',
    stats: '4,567',
    percentage: +18,
    subtitle: 'Last week analytics',
  },
  {
    icon: 'tabler-pay-check',
    color: 'success',
    title: '금월 감소한 결제모듈',
    stats: '19,860',
    percentage: -14,
    subtitle: 'Last week analytics',
  },
  {
    icon: 'tabler-pay-exclamation',
    color: 'warning',
    title: '금주 감소한 결제모듈',
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
        v-for="meta in payListMeta"
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
                
              </VCol>
              <VCol cols="12" sm="2">
                         
             </VCol>
              <VCol cols="12" sm="2">
                
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
            <div class="app-pay-search-filter d-flex align-center flex-wrap gap-4">
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
              <!-- 👉 Add pay button --> 
              <VBtn prepend-icon="tabler-plus" @click="router.replace('pay/create')">
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
                <th scope="col">가맹점 ID</th>
                <th scope="col">PG사</th>
                <th scope="col">구간</th>
                <th scope="col">모듈 타입</th>
                <th scope="col">MID</th>
                <th scope="col">TID</th>
                <th scope="col">시리얼 번호</th>
                <th scope="col">할부한도</th>
                <th scope="col">통신비</th>
                <th scope="col">정산일</th>
                <th scope="col">정산주체</th>
                <th scope="col">매출미달 차감금</th>
                <th scope="col">개통일</th>
                <th scope="col">출고일</th>
                <th scope="col">출고 상태</th>
                <th scope="col">결제 한도</th>
                <th scope="col">비고</th>
                <th scope="col">생성일</th>
                <th scope="col">수정/삭제</th>
              </tr>
            </thead>
            <!-- 👉 table body -->
            <tbody>
              <tr v-for="pay in pays" :key="pay.id" style="height: 3.75rem;">
                <td><span>{{ pay.id }}</span></td>
                <td>
                    <span>
                        {{ pay.mcht_id+" / "}}                
                        <VChip
                            label
                            size="small"
                            class="text-capitalize"
                        >
                            {{ +pay.mcht_id+"%" }}
                        </VChip>
                    </span>
                </td>
                <td>
                  <span>
                    {{ pay.mcht_id+" / "}}                
                    <VChip
                        label
                        size="small"
                        class="text-capitalize"
                    >
                        {{ +pay.mcht_id+"%" }}
                    </VChip>
                </span>
                </td>
                <td>
                  <span>{{ pay.mcht_id }}</span>
                </td>
                <td>
                  <span>{{ pay.mcht_id }}</span>
                </td>
                <td>
                  <span>{{ pay.mcht_id }}</span>
                </td>
                <td>
                  <span>{{ pay.mcht_id }}</span>
                </td>
                <td>
                  <span>{{ pay.mcht_id }}</span>
                </td>
                <td>
                  <span>{{ pay.mcht_id }}</span>
                </td>
                <td>
                  <span>{{ pay.mcht_id }}</span>
                </td>
                <td>
                  <span>{{ pay.mcht_id }}</span>
                </td>
                <td>
                  <span>{{ pay.mcht_id }}</span>
                </td>
                <!-- 👉 Plan -->
                <td>
                  <span>{{ pay.mcht_id }}</span>
                </td>
                <!-- 👉 Billing -->
                <td>
                    <span>{{ pay.mcht_id }}</span>
                </td>
                <td>
                    <span>{{ pay.mcht_id }}</span>
                </td>
                <td>
                    <span>{{ pay.mcht_id }}</span>
                </td>
                <td>
                    <span>{{ pay.mcht_id }}</span>
                </td>
                <td>
                    <span>{{ pay.mcht_id }}</span>
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
            <tfoot v-show="!pays.length">
              <tr>
                <td
                  colspan="20"
                  class="text-center"
                  style="padding: 1em;"
                >
                  결제모듈이 존재하지 않습니다. 
                  <br>
                  <br>
                  최초 사용자이시면 연동 정보 관리 -> PG사 관리에서 PG사와 구간을 등록해주세요.
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
.app-pay-search-filter {
  inline-size: 31.6rem;
}

.text-capitalize {
  text-transform: capitalize;
}

.pay-list-name:not(:hover) {
  color: rgba(var(--v-theme-on-background), var(--v-high-emphasis-opacity));
}
</style>
