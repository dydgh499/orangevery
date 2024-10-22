<script lang="ts" setup>
import IPDetailDialog from '@/layouts/dialogs/services/IPDetailDialog.vue';
import LastLoginDialog from '@/layouts/dialogs/services/LastLoginDialog.vue';
import SkeletonBox from '@/layouts/utils/SkeletonBox.vue';
import { StatusColorSetter } from '@/views/searcher';
import { connection_types, getLevelByChipColor } from '@/views/services/abnormal-connection-histories/useStore';
import { operator_levels } from '@/views/services/operators/useStore';
import type { AbnormalConnectionHistory, Popup } from '@/views/types';
import { allLevels, axios, getUserLevel } from '@axios';
import { PopupEvent } from '@core/utils/popup';

interface WorkStatusByTimezone {
    type: number,
    yesterday_afternoon: string,
    yesterday_evening: string,
    today_last_night: string,
    today_noon: string
}

interface LoginHistory {
    profile_img: string,
    nick_name: string,
    level: number,
    created_at: string,
}

const detailWorkStatusDialog = <any>inject('detailWorkStatusDialog')
const iPDetailDialog = ref()
const lastLoginDialog = ref()

const { setOpenStatus, init } = PopupEvent('secure-report/hide/')
const popup = ref(<Popup>({
    id: 0,
    visible: false,
    is_hide: false,
}))

const histories = ref(<AbnormalConnectionHistory[]>([]))
const work_status_by_timezone = ref(<WorkStatusByTimezone[]>([]))
const login_histories = ref(<LoginHistory[]>([]))

const abnormal_s_at = ref(<string>(''))
const work_status_s_at = ref(<string>(''))
const current_at = ref(<string>(''))

const setSecureReport = async () => {
    init(popup.value)
    if(getUserLevel() >= 35 && popup.value.visible) {
        try {
            const res = await axios.get('/api/v1/manager/services/abnormal-connection-histories/secure-report')
            histories.value = res.data.abnormal_connections
            login_histories.value = res.data.login_histories
            work_status_by_timezone.value = res.data.work_status_by_timezone

            current_at.value = res.data.current_at
            abnormal_s_at.value = res.data.abnormal_s_at
            work_status_s_at.value = res.data.work_status_s_at
        }
        catch (error: any) {}   
    }
}

const timeZoneTotalCount = (detail_time_type: number) => {
    const time_types = ['yesterday_afternoon', 'yesterday_evening', 'today_last_night', 'today_noon']
    let count = 0;
    for (let i = 0; i < 4; i++)  {
        count += parseInt(work_status_by_timezone.value[i][time_types[detail_time_type]])
    }
    return count
}

setSecureReport()
</script>
<template>
    <VDialog v-model="popup.visible" persistent max-width="1600">
        <div class="button-container">
            <VCheckbox v-model="popup.is_hide" class="check-label not-open-today" label="오늘 안보기" />
            <DialogCloseBtn @click="setOpenStatus(popup)" />
        </div>
        <VCard title="보안 리포트">
            <h5 style="margin-left: 2em;" class="text-error">확인되지 않은 IP의 이상접속이 발견되면 개발사측에 공유하여 대처될 수 있도록 공유바랍니다. </h5>
            <VCardText>
                <VRow>
                    <VCol md="6" cols="12">
                        <b>
                            <div class="d-flex justify-space-between">
                                <h6 class="text-base font-weight-semibold me-3">
                                    시간대별 작업 개수
                                </h6>
                                <span class="text-sm">
                                    조회시간: {{ work_status_s_at }} ~
                                </span>
                            </div>
                        </b>
                        <VTable class="text-no-wrap">
                            <thead>
                                <tr>
                                    <th class='list-square'>작업시간대</th>
                                    <th class='list-square'>결제모듈</th>
                                    <th class='list-square'>가맹점</th>
                                    <th class='list-square'>영업점</th>
                                    <th class='list-square'>운영자</th>
                                    <!--
                                        <th class='list-square'>상세보기</th>
                                    -->
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <th class='list-square'>12:00 ~ 18:00 (작일)</th>
                                    <td class='list-square' v-for="key in 4" :key="'12-18-' + key">
                                        <span v-if="work_status_by_timezone.length === 4">
                                            {{ parseInt(work_status_by_timezone[key - 1].yesterday_afternoon).toLocaleString() }}
                                        </span>
                                        <SkeletonBox :width="'3em'" v-else/>
                                    </td>
                                    <!--
                                        <td class='list-square'>
                                            <VBtn v-if="timeZoneTotalCount(0)" size="small" variant="tonal" @click="detailWorkStatusDialog.show(0, '12:00 - 18:00 (작일)')">상세보기</VBtn>
                                        </td>
                                    -->
                                </tr>
                                <tr>
                                    <th class='list-square'>18:00 ~ 24:00 (작일)</th>
                                    <td class='list-square' v-for="key in 4" :key="'18-24-' + key">
                                        <span v-if="work_status_by_timezone.length === 4">
                                            {{ parseInt(work_status_by_timezone[key - 1].yesterday_evening).toLocaleString() }}
                                        </span>
                                        <SkeletonBox :width="'3em'" v-else/>
                                    </td>
                                    <!--
                                    <td class='list-square'>
                                        <VBtn v-if="timeZoneTotalCount(1)" size="small" variant="tonal" @click="detailWorkStatusDialog.show(1, '18:00 - 24:00 (작일)')">상세보기</VBtn>                                        
                                    </td>
                                    -->
                                </tr>
                                <tr>
                                    <th class='list-square'>00:00 ~ 06:00 (금일)</th>
                                    <td class='list-square' v-for="key in 4" :key="'00-06-' + key">
                                        <span v-if="work_status_by_timezone.length === 4">
                                            {{ parseInt(work_status_by_timezone[key - 1].today_last_night).toLocaleString() }}
                                        </span>
                                        <SkeletonBox :width="'3em'" v-else/>
                                    </td>
                                </tr>
                                <tr>
                                    <th class='list-square'>06:00 ~ 12:00 (금일)</th>
                                    <td class='list-square' v-for="key in 4" :key="'06-12-' + key">
                                        <span v-if="work_status_by_timezone.length === 4">
                                            {{ parseInt(work_status_by_timezone[key - 1].today_noon).toLocaleString() }}
                                        </span>
                                        <SkeletonBox :width="'3em'" v-else/>
                                    </td>
                                </tr>
                            </tbody>
                        </VTable>
                    </VCol>
                    <VCol md="6" cols="12">
                        <b>
                            <div class="d-flex justify-space-between">
                                <h6 class="text-base font-weight-semibold me-3">
                                    운영자 로그인 이력
                                </h6>
                                <span class="text-sm">
                                    조회시간: {{ abnormal_s_at }} ~
                                </span>
                            </div>
                        </b>
                        <VTable class="text-no-wrap">
                            <thead>
                                <tr>
                                    <th class='list-square'>프로필</th>
                                    <th class='list-square'>성명</th>
                                    <th class='list-square'>등급</th>
                                    <th class='list-square'>로그인시간</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-for="(history, key) in login_histories" :key="key"  v-if="work_status_by_timezone.length === 4">
                                    <td class='list-square'>
                                        <VAvatar :image="history.profile_img" class="me-3 preview"/>
                                    </td>
                                    <td class='list-square'>{{ history.nick_name }}</td>
                                    <td class='list-square'>
                                        <VChip v-if="history.level"
                                        :color="history.level === 35 ? 'default' : 'primary'">
                                            {{ operator_levels.find(obj => obj.id === history.level)?.title }}
                                        </VChip>
                                    </td>
                                    <td class='list-square'>{{ history.created_at }}</td>
                                </tr>
                                <tr v-for="key in 4" v-else>
                                    <td class='list-square'><SkeletonBox :width="'3em'"/></td>
                                    <td class='list-square'><SkeletonBox :width="'3em'"/></td>
                                    <td class='list-square'><SkeletonBox :width="'3em'"/></td>
                                    <td class='list-square'><SkeletonBox :width="'5em'"/></td>
                                </tr>
                            </tbody>
                            <tfoot v-if="!Boolean(login_histories.length) && work_status_by_timezone.length === 4">
                                <tr>
                                    <td :colspan="4" class='list-square' style="border: 0;">
                                        로그인 이력이 존재하지 않습니다.
                                    </td>
                                </tr>
                            </tfoot>
                        </VTable>
                    </VCol>
                </VRow>
            </VCardText>
            <VCardText>
                <b>
                    <div class="d-flex justify-space-between">
                        <h6 class="text-base font-weight-semibold me-3">
                            이상접속 이력
                        </h6>
                        <span class="text-sm">
                            조회시간: {{ abnormal_s_at }} ~
                        </span>
                    </div>
                </b>
                <VTable class="text-no-wrap" style="height: 500px !important;">
                    <thead>
                        <tr>
                            <th class='list-square'>접근타입</th>
                            <th class='list-square'>접근시간</th>
                            <th class='list-square'>조치사항</th>
                            <th class='list-square'>메모사항</th>
                            <th class='list-square'>등급</th>
                            <th class='list-square'>대상</th>
                            <th class='list-square'>값</th>
                            <th class='list-square'>접속 IP</th>    <!-- ip 상세보기 이동통신 여부-->
                            <th class='list-square'>추가기능</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="(history, key) in histories" :key="key" v-if="work_status_by_timezone.length === 4">
                            <td class='list-square'>
                                <VChip :color="StatusColorSetter().getSelectIdColor(history['connection_type'])">
                                    {{ connection_types.find(obj => obj.id === history['connection_type'])?.title }}
                                </VChip>    
                            </td>
                            <td class='list-square'>
                                {{ history['created_at'] }}
                            </td>
                            <td class='list-square'>
                                <span v-html="history['action'].replace('(', '<br>(')"></span>
                            </td>
                            <td class='list-square'>
                                <span v-html="history['comment'].replace('(', '<br>(')"></span>
                            </td>
                            <td class='list-square'>
                                <VChip v-if="history['target_level']"
                                    :color="history['target_level'] >= 35 ? (history['target_level'] ? 'default' : 'primary') : StatusColorSetter().getSelectIdColor(getLevelByChipColor(history['target_level']))">
                                        {{ allLevels().find(obj => obj.id === history['target_level'])?.title }}
                                </VChip>
                                <span v-else>세션없음</span>
                            </td>
                            <td class='list-square'>
                                <div class="report-content">
                                    {{ history['target_key'] }}
                                    <VTooltip activator="parent" location="top" transition="scale-transition">
                                        <span>{{ history['target_key'] }}</span>
                                    </VTooltip>
                                </div>
                            </td>
                            <td class='list-square'>
                                <div class="report-content">
                                    {{ history['target_value'] }}
                                    <VTooltip activator="parent" location="top" transition="scale-transition">
                                        <span>{{ history['target_value'] }}</span>
                                    </VTooltip>
                                </div>
                            </td>
                            <td class='list-square'>
                                <div style="display: inline-flex; flex-direction: row;">
                                    <div style="display: inline-flex; flex-direction: column; justify-content: space-evenly;">
                                        <b>{{ history['request_ip']  }}</b>
                                        <span v-if="history['mobile_type'] !== ''">
                                            ({{ history['mobile_type'] }})
                                        </span>
                                    </div>
                                </div>
                            </td>
                            <td class='list-square'>
                                <div style="display: inline-flex; flex-direction: column; align-items: center; justify-content: space-evenly; padding: 0.25em;">
                                    <VBtn size="small" variant="tonal" @click="iPDetailDialog.show(history['request_detail'])" style="margin-bottom: 0.25em;">상세보기</VBtn>
                                    <VBtn size="small" variant="tonal" color="error" @click="lastLoginDialog.show(history['request_ip'])">로그인 목록</VBtn>
                                </div>
                            </td>
                        </tr>                        
                        <tr v-for="key in 4" v-else>
                            <td class='list-square'><SkeletonBox :width="'5em'"/></td>
                            <td class='list-square'><SkeletonBox :width="'5em'"/></td>
                            <td class='list-square'><SkeletonBox :width="'5em'"/></td>
                            <td class='list-square'><SkeletonBox :width="'5em'"/></td>
                            <td class='list-square'><SkeletonBox :width="'3em'"/></td>
                            <td class='list-square'><SkeletonBox :width="'3em'"/></td>
                            <td class='list-square'><SkeletonBox :width="'15em'"/></td>
                            <td class='list-square'><SkeletonBox :width="'5em'"/></td>
                            <td class='list-square'><SkeletonBox :width="'5em'"/></td>
                        </tr>
                    </tbody>
                    <tfoot v-if="!Boolean(histories.length) && work_status_by_timezone.length === 4">
                        <tr>
                            <td :colspan="9" class='list-square' style="border: 0;">
                                이상접속 이력이 존재하지 않습니다.
                            </td>
                        </tr>
                    </tfoot>
                </VTable>
            </VCardText>
        </VCard>
    </VDialog>
    <IPDetailDialog ref="iPDetailDialog"/>
    <LastLoginDialog ref="lastLoginDialog"/>
</template>
<style scoped>
.report-content {
  overflow: hidden;
  inline-size: 200px !important;
  text-overflow: ellipsis;
  white-space: nowrap;
  word-break: break-all;
}

:deep(.v-table__wrapper) {
  block-size: 250px !important;
}

:deep(.v-table__wrapper) > th,
td {
  font-size: 0.5em;
}
</style>
