<script lang="ts" setup>
import { StatusColorSetter } from '@/views/searcher';
import { getLevelColor } from '@/views/services/abnormal-connection-histories/useStore';
import { history_types } from '@/views/services/activity-histories/useStore';
import type { ActivityHistory } from '@/views/types';
import { allLevels, axios } from '@axios';

interface DetailWorkContent extends ActivityHistory {
    level: number,
}

const visible = ref(false)
const work_time_comment = ref('')
const activity_histories = ref(<DetailWorkContent[]>([]))

const show = async (detail_time_type: number, _work_time_comment: string) => {
    const res = await axios.get('/api/v1/manager/services/abnormal-connection-histories/secure-report/detail-work-status', {
        params: {detail_time_type: detail_time_type}
    })
    activity_histories.value = res.data
    work_time_comment.value = _work_time_comment
    visible.value = true
}

defineExpose({
    show
});
</script>
<template>
    <VDialog v-model="visible" persistent max-width="1200">
        <DialogCloseBtn @click="visible = !visible" />
        <VCard :title="`${work_time_comment} 상세 작업내용`">
            <VCardText>
                <VTable class="text-no-wrap">
                    <thead>
                        <tr>
                            <th class='list-square'>작업번호<br>(운영자 이력번호)</th>
                            <th class='list-square'>작업자</th>
                            <th class='list-square'>등급</th>
                            <th class='list-square'>활동종류</th>
                            <th class='list-square'>적용대상</th>
                            <th class='list-square'>활동타입</th>
                            <th class='list-square'>작업시간</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="(history, key) in activity_histories" :key="key">
                            <td class='list-square'>
                                <b># {{ history.id }}</b>
                            </td>
                            <td class='list-square'>
                                <VAvatar :image="history.profile_img" class="me-3 preview"/>
                                <br>
                                {{ history.nick_name }}
                            </td>
                            <td class='list-square'>
                                <VChip v-if="history.level"
                                :color="getLevelColor(history.level)">
                                    {{ allLevels().find(obj => obj.id === history.level)?.title }}
                                </VChip>
                            </td>
                            <td class='list-square'>
                                {{ history.history_target }}
                            </td>
                            <td class='list-square'>
                                {{ history.history_title }}
                            </td>
                            <td class='list-square'>
                                <VChip
                                    :color="StatusColorSetter().getSelectIdColor(history_types.find(obj => obj.id === history.history_type)?.id as number)">
                                    {{ history_types.find(obj => obj.id === history.history_type)?.title }}
                                </VChip>
                            </td>
                            <td class='list-square'>
                                {{ history.created_at }}
                            </td>
                        </tr>
                    </tbody>
                    <tfoot v-if="!Boolean(activity_histories.length)">
                        <tr>
                            <td :colspan="8" class='list-square' style="border: 0;">
                                작업내역이 존재하지 않습니다.
                            </td>
                        </tr>
                    </tfoot>
                </VTable>
            </VCardText>
        </VCard>
    </VDialog>
</template>
<style scoped>
:deep(.v-table__wrapper) {
  block-size: auto !important;
}
</style>
