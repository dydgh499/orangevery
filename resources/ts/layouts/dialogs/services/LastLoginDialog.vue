<script setup lang="ts">
import { useRequestStore } from '@/views/request';
import { getLevelColor } from '@/views/services/abnormal-connection-histories/useStore';
import { allLevels } from '@axios';

interface LoginInfo {
    level?: number,
    mutual?: string,
    user_name?: string,
    nick_name?: string,
    last_login_at?: string,
}

const { get } = useRequestStore()
const visible = ref(false)
const login_infos = ref(<LoginInfo[]>([]))
const show = async (connection_ip: string) => {
    const res = await get(`/api/v1/manager/services/abnormal-connection-histories/last-login`, {
        params: {
            connection_ip: connection_ip,
        }
    })
    login_infos.value = res.data
    visible.value = true
}

defineExpose({
    show
});
</script>
<template>
    <VDialog v-model="visible" max-width="900">
        <!-- Dialog close btn -->
        <DialogCloseBtn @click="visible = false" />
        <!-- Dialog Content -->
        <VCard title="로그인 목록">
            <VCardText>
                <VRow style="margin-left: 0.5em;">
                    <span class="text-sm">
                        최근 해당 IP로 로그인이 되었던 계정들을 조회합니다.
                    </span>
                </VRow>
                <br>
                <VTable class="text-no-wrap" style="width: 100%;">
                    <thead>
                        <tr>
                            <th class='list-square'>등급</th>
                            <th class='list-square'>상호</th>
                            <th class='list-square'>ID</th>
                            <th class='list-square'>대표자명</th>
                            <th class='list-square'>마지막 로그인 시간</th>
                        </tr>
                    </thead>
                    <tbody>
                        <template v-for="(item, index) in login_infos">
                            <tr>
                                <td class='list-square'>
                                    <VChip
                                    :color="getLevelColor(item.level)">
                                        {{ allLevels().find(obj => obj.id === item.level)?.title  }}
                                    </VChip>
                                </td>
                                <td class='list-square'>{{ item.mutual }}</td>
                                <td class='list-square'>{{ item.user_name }}</td>
                                <td class='list-square'>{{ item.nick_name }}</td>
                                <td class='list-square'>{{ item.last_login_at }}</td>
                            </tr>
                        </template>
                    </tbody>
                    <tfoot v-if="!Boolean(login_infos.length)">
                        <tr>
                            <td :colspan="5" class='list-square' style="border: 0;">
                                로그인 되었던 이력을 찾을 수 없습니다.
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
