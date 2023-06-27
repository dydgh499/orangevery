<script setup lang="ts">
import avatar1 from '@images/avatars/avatar_1.jpg'
import avatar2 from '@images/avatars/avatar_2.jpg'
</script>

<template>
  <VCard title="Activity Timeline">
    <template #append>
      <div class="me-n2">
        <VBtn
          icon
          color="default"
          size="x-small"
          variant="plain"
        >
          <VIcon
            size="22"
            icon="tabler-dots-vertical"
          />

          <VMenu activator="parent">
            <VList>
              <VListItem
                v-for="(item, index) in ['Refresh', 'Download', 'View All']"
                :key="index"
                :value="index"
              >
                <VListItemTitle>{{ item }}</VListItemTitle>
              </VListItem>
            </VList>
          </VMenu>
        </VBtn>
      </div>
    </template>

    <VCardText>
      <VTimeline
        side="end"
        align="start"
        truncate-line="both"
        density="compact"
        class="v-timeline-density-compact"
      >
        <VTimelineItem
          dot-color="primary"
          size="x-small"
        >
          <!-- 👉 Header -->
          <div class="d-flex justify-space-between">
            <h6 class="text-base font-weight-semibold me-3">
              관리자 로그인
            </h6>
            <span class="text-sm text-disabled">Today</span>
          </div>

          <!-- 👉 Content -->
          <p class="mb-1">
          </p>
          <div class="d-flex align-center">
            <VAvatar
              :image="avatar1"
              class="me-3"
            />
            <div>
              <p class="font-weight-semibold mb-0">
                홍길동
              </p>
              <span>CEO</span>
            </div>
          </div>
        </VTimelineItem>

        <VTimelineItem
          dot-color="success"
          size="x-small"
        >
          <!-- 👉 Header -->
          <div class="d-flex justify-space-between">
            <h6 class="text-base font-weight-semibold me-3">
              가맹점 등록
            </h6>
            <span class="text-sm text-disabled">2 Day Ago</span>
          </div>

          <!-- 👉 Content -->
          <p class="mb-1">
            하위 관리자 1
          </p>
        </VTimelineItem>

        <VTimelineItem
          dot-color="error"
          size="x-small"
        >
          <!-- 👉 Header -->
          <div class="d-flex justify-space-between">
            <h6 class="text-base font-weight-semibold me-3">
              영업점 수수료 변경
            </h6>
            <span class="text-sm text-disabled">6 Day Ago</span>
          </div>

          <!-- 👉 Content -->
          <p class="mb-1">
            <span class="me-2">하위 관리자 2</span>
            <VAvatar
              :image="avatar2"
              size="20"
            />
          </p>
        </VTimelineItem>

        <VTimelineItem
          dot-color="info"
          size="x-small"
        >
          <!-- 👉 Header -->
          <div class="d-flex justify-space-between">
            <h6 class="text-base font-weight-semibold me-3">
              단말기 등록
            </h6>
            <span class="text-sm text-disabled">10 Day Ago</span>
          </div>

          <!-- 👉 Content -->
          <p class="mb-1">
            하위 관리자 3
          </p>
        </VTimelineItem>
      </VTimeline>
    </VCardText>
  </VCard>
</template>
