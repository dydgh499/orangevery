<script setup lang="ts">
import { useCRMStore } from '@/views/dashboards/crm/crm'
import { history_types } from '@/views/services/operator-histories/useStore'
import ImageDialog from '@/layouts/dialogs/ImageDialog.vue'
import SkeletonBox from '@/layouts/utils/SkeletonBox.vue'

const { operator_histories } = useCRMStore()
const is_skeleton = <any>(inject('is_skeleton'))
const imageDialog = ref()
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
const showAvatar = (preview: string) => {
    imageDialog.value.show(preview)
}

</script>
<template>
    <div>
        <VCard title="운영자 활동이력">
            <VCardText style="height: 531px !important;">
                <VTimeline side="end" align="start" truncate-line="both" density="compact" class="v-timeline-density-compact">
                    <template v-if="is_skeleton">
                        <VTimelineItem v-for="(operator_history, _index) in 5" :key="_index" size="x-small" >
                        <div class="d-flex justify-space-between">
                            <h6 class="text-base font-weight-semibold me-3" style="display: flex; align-items: center;">
                                <SkeletonBox :width="'10em'"/>
                                <span>-</span>
                                <SkeletonBox/>
                            </h6>
                            <span class="text-sm">
                                <SkeletonBox :width="'10em'"/>
                            </span>
                        </div>
                        <!-- 👉 Content -->
                        <div class="d-flex align-center mt-2">
                                <SkeletonBox :width="'3em'" :height="'3em'"/>
                            <div>
                                <p class="font-weight-semibold mb-0" style="margin-left: 1em;">
                                    <SkeletonBox :width="'5em'"/>
                                </p>
                            </div>
                        </div>
                    </VTimelineItem>
                    </template>
                    <template v-else>
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
                                <VAvatar :image="operator_history.profile_img" class="me-3 preview" @click="showAvatar(operator_history.profile_img)"/>
                                <div>
                                    <p class="font-weight-semibold mb-0">
                                        <span>{{ operator_history.nick_name }}</span>                             
                                    </p>
                                </div>
                            </div>
                        </VTimelineItem>
                    </template>

                    <VTimelineItem v-show="!Boolean(operator_histories.length) && is_skeleton" size="x-small">
                        <div class="d-flex justify-space-between">
                            <span class="text-sm">
                                최근 운영자 활동이력이 존재하지 않습니다.
                            </span>
                        </div>
                    </VTimelineItem>
                </VTimeline>
            </VCardText>
        </VCard>
        <ImageDialog ref="imageDialog" :style="`inline-size:20em !important;`"/>
    </div>
</template>
