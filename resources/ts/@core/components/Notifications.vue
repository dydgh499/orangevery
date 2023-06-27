<script lang="ts" setup>
import type { Anchor } from 'vuetify/lib/components'
import type { Notification } from '@layouts/types'

interface Props {
    notifications: Notification[]
    badgeProps?: unknown
    location?: Anchor
}
const props = withDefaults(defineProps<Props>(), {
    location: 'bottom end',
    badgeProps: undefined,
})

const router = useRouter()
const getLocaleString = (update_dt: Date) => {
    return update_dt.toLocaleString('ko-KR', {
        year: 'numeric',
        month: 'numeric',
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
    })
}
const getTitleStatus = computed(() => {
    return props.notifications.length ? '최근 1:1 문의 요청의 건' : '최근 5일간 문의가 존재하지 않습니다! 🎉'
})

</script>

<template>
    <VBadge :model-value="!!props.badgeProps" v-bind="props.badgeProps">
        <VBtn icon variant="text" color="default" size="small">
            <VBadge :model-value="!!props.notifications.length" color="error" :content="props.notifications.length">
                <VIcon icon="tabler-bell" size="24" />
            </VBadge>

            <VMenu activator="parent" width="380px" :location="props.location" offset="14px">
                <VList class="py-0">
                    <!-- 👉 Header -->
                    <VListItem :title="getTitleStatus" class="notification-section" height="48px">
                        <template #append>
                            <VChip v-if="props.notifications.length" color="primary" size="small">
                                {{ props.notifications.length }} New
                            </VChip>
                        </template>
                    </VListItem>
                    <VDivider />

                    <!-- 👉 Notifications list -->
                    <template v-for="notification in props.notifications" :key="notification.title">
                        <router-link :to="'/posts/reply?parent_id=' + notification.id" class="custom-link">
                            <VListItem :title="notification.title" :subtitle="notification.writer" link lines="one"
                                min-height="66px">
                                <!-- Slot: Append -->
                                <template #append>
                                    <small class="whitespace-no-wrap text-medium-emphasis">
                                        {{ getLocaleString(notification.updated_at) }}
                                    </small>
                                </template>
                            </VListItem>
                            <VTooltip activator="parent" location="start" transition="scale-transition">
                                <span>클릭해서 답장하러 가기</span>
                            </VTooltip>
                        </router-link>
                        <VDivider />
                    </template>
                </VList>
            </VMenu>
        </VBtn>
    </VBadge>
</template>

<style lang="scss">
.notification-section {
  padding: 14px !important;
}

.custom-link {
  color: inherit;
  text-decoration: none;
}
</style>
