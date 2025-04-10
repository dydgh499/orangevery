<script setup lang="ts">
import ImageDialog from '@/layouts/dialogs/utils/ImageDialog.vue';
import SkeletonBox from '@/layouts/utils/SkeletonBox.vue';
import { useCRMStore } from '@/views/dashboards/crm/crm';
import { StatusColorSetter } from '@/views/searcher';
import { getLevelColor } from '@/views/services/abnormal-connection-histories/useStore';
import { historyLevels, history_types } from '@/views/services/activity-histories/useStore';

const { activity_histories } = useCRMStore()
const is_skeleton = <any>(inject('is_skeleton'))
const imageDialog = ref()

const showAvatar = (preview: string) => {
    imageDialog.value.show(preview)
}

</script>
<template>
    <div>
        <VCard title="최근 활동이력">
            <VCardText style="height: 531px !important;">
                <VTimeline side="end" align="start" truncate-line="both" density="compact" class="v-timeline-density-compact" style="overflow: auto !important;">
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
                        <VTimelineItem v-for="(operator_history, key, index) in activity_histories" :key="key"
                            :dot-color="StatusColorSetter().getSelectIdColor(operator_history.history_type)" size="x-small">
                            <!-- 👉 Header -->
                            <div class="d-flex justify-space-between">
                                <h6 class="text-base font-weight-semibold me-3">
                                    {{ operator_history.history_target }}
                                    {{ operator_history.history_title ? " - "+operator_history.history_title : ''}}
                                    <VChip :color="StatusColorSetter().getSelectIdColor(operator_history.history_type)">
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
                                <div class="d-flex" style="width: 100%; justify-content: space-between;">
                                    <p class="font-weight-semibold mb-0">
                                        <span>{{ operator_history.nick_name }}</span>    
                                    </p>
                                    <VChip 
                                        :color="getLevelColor(operator_history.level)" 
                                        size="small">
                                        {{ historyLevels().find(obj => obj.id === operator_history.level)?.title  }}
                                    </VChip>
                                </div>
                            </div>
                            <VDivider style="margin-top: 1em;"/>
                        </VTimelineItem>
                        <VTimelineItem v-show="!Boolean(activity_histories.length) && !is_skeleton" size="x-small">
                            <div class="d-flex justify-space-between">
                                <span class="text-sm">
                                    최근 활동이력이 존재하지 않습니다.
                                </span>
                            </div>
                        </VTimelineItem>
                    </template>
                </VTimeline>
            </VCardText>
        </VCard>
        <ImageDialog ref="imageDialog" :style="`inline-size:20em !important;`"/>
    </div>
</template>
