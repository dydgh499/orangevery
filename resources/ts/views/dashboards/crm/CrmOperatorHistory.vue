<script setup lang="ts">
import { useCRMStore } from '@/views/dashboards/crm/crm'
import { history_types } from '@/views/services/operator-histories/useStore'

const { operator_histories } = useCRMStore()
const getSelectIdColor = (id: number | undefined) => {
    if (id == 0)
        return "default"
    else if (id == 1)
        return "primary"
    else if (id == 2)
        return "success"
    else if (id == 3)
        return "info"
    else if (id == 4)
        return "warning"
    else if (id == 5)
        return "error"
    else
        return 'default'
}
</script>
<template>
    <VCard title="운영자 활동이력">
        <VCardText>
            <VTimeline side="end" align="start" truncate-line="both" density="compact" class="v-timeline-density-compact">
                <VTimelineItem v-for="(operator_history, key, index) in operator_histories" :key="key"
                    :dot-color="getSelectIdColor(operator_history.history_type)" size="x-small">
                    <!-- 👉 Header -->
                    <div class="d-flex justify-space-between">
                        <h6 class="text-base font-weight-semibold me-3">
                            {{ operator_history.history_target }}
                            {{ operator_history.history_title ? " - "+operator_history.history_title : ''}}
                            <VChip :color="getSelectIdColor(operator_history.history_type)">
                                {{ history_types.find(history_type => history_type['id'] === operator_history.history_type)?.title  }}
                            </VChip>   
                        </h6>
                        <span class="text-sm">
                            {{ operator_history.created_at }}
                        </span>
                    </div>
                    <!-- 👉 Content -->
                    <div class="d-flex align-center mt-2">
                        <VAvatar :image="operator_history.profile_img" class="me-3" />
                        <div>
                            <p class="font-weight-semibold mb-0">
                                <span>{{ operator_history.nick_name }}</span>                             
                            </p>
                        </div>
                    </div>
                </VTimelineItem>
                <VTimelineItem v-show="!Boolean(operator_histories.length)" size="x-small">
                    <div class="d-flex justify-space-between">
                        <span class="text-sm">
                            최근 운영자 활동이력이 존재하지 않습니다.
                        </span>
                    </div>
                </VTimelineItem>
            </VTimeline>
        </VCardText>
    </VCard>
</template>
