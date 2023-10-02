<script lang="ts" setup>
import BaseQuestionTooltip from '@/layouts/tooltips/BaseQuestionTooltip.vue'
import CreateHalfVCol from '@/layouts/utils/CreateHalfVCol.vue'
import UnderAutoSettingCard from '@/views/salesforces/under-auto-settings/UnderAutoSettingCard.vue'
import { getLevelByIndex } from '@/views/salesforces/useStore'
import type { Salesforce, UnderAutoSetting } from '@/views/types'

interface Props {
    item: Salesforce,
}
const props = defineProps<Props>()
const under_auto_settings = ref<UnderAutoSetting[]>(props.item.under_auto_settings || [])
const new_under_auto_settings = reactive<UnderAutoSetting[]>([])
const addNewUnderAutoSetting = () => {
    const under_auto_setting = <UnderAutoSetting>({
        id: 0,
        sales_id: props.item.id,
        sales5_id: null,
        sales4_id: null,
        sales3_id: null,
        sales2_id: null,
        sales1_id: null,
        sales0_id: null,
        sales5_fee: 0,
        sales4_fee: 0,
        sales3_fee: 0,
        sales2_fee: 0,
        sales1_fee: 0,
        sales0_fee: 0,
        note: "D+1",
    })
    under_auto_setting['sales'+getLevelByIndex(props.item.level)+"_id"] = props.item.id
    new_under_auto_settings.push(under_auto_setting)
}
</script>
<template>
    <VCardTitle style="margin: 1em 0;">
        <BaseQuestionTooltip :location="'top'" :text="'하위 수수료율 자동세팅'" :content="'가맹점 수수료율 세팅시 해당정보가 자동반영됩니다.'">
        </BaseQuestionTooltip>
    </VCardTitle>
    <div v-if="props.item.id != 0" style="width: 100%;">
        <UnderAutoSettingCard v-for="under_auto_setting in under_auto_settings" :key="under_auto_setting.id" style="margin-top: 1em;" :item="under_auto_setting" :salesforce="props.item"/>
        <UnderAutoSettingCard v-for="(new_under_auto_setting, index) in new_under_auto_settings" :key="index" style="margin-top: 1em;" :item="new_under_auto_setting" :salesforce="props.item"/>
        <VCard style="margin-top: 1em;">
        <VCol class="d-flex gap-4">
            <VBtn type="button" style="margin-left: auto;" @click="addNewUnderAutoSetting">
                세팅정보 신규추가
                <VIcon end icon="tabler-plus" />
            </VBtn>
        </VCol>
    </VCard>
    </div>
    <div v-else style="width: 100%; text-align: center;">
        <CreateHalfVCol :mdl="0" :mdr="12">
            <template #name></template>
            <template #input>
                영업점을 추가하신 후 사용 가능합니다.
            </template>
        </CreateHalfVCol>
    </div>
</template>
