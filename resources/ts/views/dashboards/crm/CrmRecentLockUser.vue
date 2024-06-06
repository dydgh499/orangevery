<script setup lang="ts">
import SkeletonBox from '@/layouts/utils/SkeletonBox.vue';
import { useCRMStore } from '@/views/dashboards/crm/crm';
import { allLevels, getLevelByIndex } from '@axios';

const is_skeleton = <any>(inject('is_skeleton'))

const { locked_users } = useCRMStore()

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
const getLevelByChipColor = (level: number) => {
    if(level === 10)
        return 0
    else if(level >= 35)
        return getLevelByIndex(level)
    else
        return 2
}

</script>

<template>
    <VCard title="잠금된 계정">
        <VCardText style="height: 531px !important;">
                <VTimeline side="end" align="start" truncate-line="both" density="compact" class="v-timeline-density-compact" style="overflow: auto !important;">
                    <template v-if="is_skeleton">
                        <VTimelineItem v-for="(operator_history, _index) in 5" :key="_index" size="x-small" >
                            <!-- 👉 Header -->
                        <div class="d-flex justify-space-between">
                            <h6 class="text-base font-weight-semibold me-3" style="display: flex; align-items: center;">
                                <SkeletonBox :width="'3em'"/>
                                <span style="margin-left: 0.5em;"><SkeletonBox :width="'8em'"/></span>
                            </h6>
                            <span class="text-sm">
                                <SkeletonBox :width="'10em'"/>
                            </span>
                        </div>
                        <!-- 👉 Content -->
                        <div class="d-flex align-center mt-2">
                                <span> - <SkeletonBox :width="'5em'"/></span>
                            <div>
                                <p class="font-weight-semibold mb-0">
                                    <span style="margin-left: 0.5em;">(<SkeletonBox :width="'8em'"/>)</span>
                                </p>
                            </div>
                        </div>
                    </VTimelineItem>
                    </template>
                    <template v-else>
                        <VTimelineItem v-for="(user, key, index) in locked_users" :key="key"
                            :dot-color="getSelectIdColor(getLevelByChipColor(user.level))" size="x-small">
                            <!-- 👉 Header -->
                            <div class="d-flex justify-space-between">
                                <h6 class="text-base font-weight-semibold me-3">
                                    <VChip
                                        :color="getSelectIdColor(getLevelByChipColor(user.level))">
                                    {{ allLevels().find(obj => obj.id === user.level)?.title }}
                                    </VChip>
                                    {{ user.user_name }}
                                </h6>
                                <span class="text-sm">
                                    {{ user.locked_at }}
                                </span>
                            </div>
                            <!-- 👉 Content -->
                            <div class="d-flex align-center mt-2">
                                <span> - {{ user.nick_name }}</span>                    
                                <div>
                                    <p class="font-weight-semibold mb-0">
                                        <span style="margin-left: 0.5em;">{{ `(${user.phone_num})` }}</span>                             
                                    </p>
                                </div>
                            </div>
                        </VTimelineItem>
                        <VTimelineItem v-show="!Boolean(locked_users.length) && !is_skeleton" size="x-small">
                            <div class="d-flex justify-space-between">
                                <span class="text-sm">
                                    잠금된 계정이 존재하지 않습니다.
                                </span>
                            </div>
                        </VTimelineItem>
                    </template>
                </VTimeline>
        </VCardText>
    </VCard>
</template>
