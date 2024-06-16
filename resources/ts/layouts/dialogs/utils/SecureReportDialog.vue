<script lang="ts" setup>
import { StatusColorSetter } from '@/views/searcher';
import { connection_types } from '@/views/services/abnormal-connection-histories/useStore';
import type { AbnormalConnectionHistory } from '@/views/types';

const visible = ref(false)
const histories = ref(<AbnormalConnectionHistory[]>([]))

let resolveCallback: (isAgreed: boolean) => void;
const show = (_histories :AbnormalConnectionHistory[]): Promise<boolean> => {
    histories.value = _histories
    visible.value = true

    return new Promise<boolean>((resolve) => {
        resolveCallback = resolve;
    });
}

defineExpose({
    show
});
</script>
<template>
    <VDialog v-model="visible" persistent>
        <!-- Dialog close btn -->
        <DialogCloseBtn @click="visible = !visible" />
        <!-- Dialog Content -->
        <VCard title="보안 리포트">
            <VCardText>
                <VTable class="text-no-wrap" style="width: 100%;">
                    <thead>
                        <tr>
                            <th class='list-square'>접근타입</th>
                            <th class='list-square'>조치사항</th>
                            <th class='list-square'>메모사항</th>
                            <th class='list-square'>등급</th>
                            <th class='list-square'>대상</th>
                            <th class='list-square'>값</th>
                            <th class='list-square'>접속 IP</th>    <!-- ip 상세보기 이동통신 여부-->
                            <th class='list-square'>접근시간</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="(history, key) in histories" :key="key">
                            <td class='list-square'>
                                <VChip :color="StatusColorSetter().getSelectIdColor(history['connection_type'])">
                                    {{ connection_types.find(obj => obj.id === history['connection_type'])?.title }}
                                </VChip>    
                            </td>
                            <td class='list-square'>
                                {{ history['action'] }}
                            </td>
                            <td class='list-square'>
                                {{ history['comment'] }}
                            </td>
                            <td class='list-square'>
                                {{ history['target_level'] }}
                            </td>
                            <td class='list-square'>
                                {{ history['target_key'] }}
                            </td>
                            <td class='list-square'>
                                {{ history['target_value'] }}
                            </td>
                            <td class='list-square'>
                                {{ history['request_ip'] }}
                            </td>
                            <td class='list-square'>
                                {{ history['created_at'] }}
                            </td>
                        </tr>
                    </tbody>
                </VTable>
            </VCardText>
        </VCard>
    </VDialog>
</template>
