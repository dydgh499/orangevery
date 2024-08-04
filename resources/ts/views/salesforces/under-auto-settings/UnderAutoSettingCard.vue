<script lang="ts" setup>
import BaseQuestionTooltip from '@/layouts/tooltips/BaseQuestionTooltip.vue'
import { useRequestStore } from '@/views/request'
import UnderAutoSettingTr from '@/views/salesforces/under-auto-settings/UnderAutoSettingTr.vue'
import type { Salesforce, UnderAutoSetting } from '@/views/types'

interface Props {
    item: Salesforce,
}
const props = defineProps<Props>()
const { setNullRemove } = useRequestStore()
const under_auto_settings = reactive<UnderAutoSetting[]>(props.item.under_auto_settings || [])
const addNewUnderAutoSetting = () => {
    const under_auto_setting = <UnderAutoSetting>({
        id: 0,
        sales_id: props.item.id,
        sales_fee: 0,
        note: "D+1(영세)",
    })
    under_auto_settings.push(under_auto_setting)
}
watchEffect(() => {
    setNullRemove(under_auto_settings)
})
</script>
<template>
    <VCardTitle style="margin: 1em 0;">
        <BaseQuestionTooltip :location="'top'" :text="'하위 수수료율 자동세팅'" :content="'가맹점 수수료율 세팅시 해당정보가 자동반영됩니다.'">
        </BaseQuestionTooltip>
    </VCardTitle>
    <VTable style="width: 100%;margin-bottom: 1em;text-align: center;">
        <thead>
            <tr>
                <th scope="col" style="text-align: center;">No.</th>
                <th scope="col" style="text-align: center;">구간 별칭</th>
                <th scope="col" style="text-align: center;">거래 수수료율</th>
                <th scope="col" style="text-align: center;">추가/수정</th>
            </tr>
        </thead>
        <tbody>
            <UnderAutoSettingTr v-for="(item, index) in under_auto_settings"
                :key="item.id" style="margin-top: 1em;" :item="item" :index="index" />
        </tbody>
        <tfoot v-show="Boolean(props.item.id == 0)">
            <tr>
                <td colspan="4" class="text-center">
                    영업점을 추가하신 후 사용 가능합니다.
                </td>
            </tr>
        </tfoot>
    </VTable>
    <VRow v-show="Boolean(props.item.id != 0)">
        <VCol class="d-flex gap-4">
            <VBtn type="button" style="margin-left: auto;" @click="addNewUnderAutoSetting()">
                세팅정보 신규추가
                <VIcon end icon="tabler-plus" />
            </VBtn>
        </VCol>
    </VRow>
</template>
<style scoped>
:deep(.v-table__wrapper) {
  block-size: auto !important;
}
</style>
