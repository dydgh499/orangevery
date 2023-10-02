<script lang="ts" setup>
import type { UnderAutoSetting } from '@/views/types'
import { useSalesFilterStore, getIndexByLevel } from '@/views/salesforces/useStore'
import corp from '@corp'

const visible = ref(false)
const level = ref(0)
const under_auto_settings = ref(<UnderAutoSetting[]>([]))
const { sales } = useSalesFilterStore()
const levels = corp.pv_options.auth.levels

let resolveCallback: (idx: number) => void;

const show = (_under_auto_settings: UnderAutoSetting[], _level: number): Promise<number> => {
    under_auto_settings.value = _under_auto_settings
    level.value = _level
    visible.value = true

    return new Promise<number>((resolve) => {
        resolveCallback = resolve;
    });
}

const selected = (idx: number) => {
    visible.value = false
    resolveCallback(idx)
}

const getSalesName = (idx:number, under_auto_setting:UnderAutoSetting) => {
    const salesforce = sales[idx].value.find(obj => obj.id == under_auto_setting['sales'+(idx)+'_id'])
    return salesforce ? salesforce.sales_name : ''
}
defineExpose({
    show
});
</script>
<template>
    <VDialog v-model="visible" persistent class="v-dialog-sm">
        <!-- Dialog Content -->
        <VCard title="적용할 영업점 자동세팅 포멧을 선택해주세요.">
            <VCardText>
                <template v-for="(under_auto_setting, key) in under_auto_settings" :key="key">
                    <VBtn @click="selected(key)">
                        <span style="font-weight: bold;" @click=" ">{{ under_auto_setting.note }}</span>
                    </VBtn>                    
                    <VTable class="text-no-wrap" style="width: 100%;">
                        <thead>
                            <tr>
                                <th class='list-square'>등급</th>
                                <th class='list-square'>영업점 상호</th>
                                <th class='list-square'>수수료</th>
                            </tr>
                        </thead>
                        <tbody>
                            <template v-for="i in 6" :key="i">
                                <tr v-if="levels['sales'+(6-i)+'_use'] && i >= level">
                                    <td class='list-square'>{{ levels['sales'+(6-i)+'_name'] }}</td>
                                    <td class='list-square'>{{ getSalesName(6-i, under_auto_setting) }}</td>
                                    <td class='list-square'>{{ under_auto_setting['sales'+(6-i)+'_fee'] }}</td>
                                </tr>
                            </template>
                        </tbody>
                    </VTable>
                    <br>
                </template>
            </VCardText>
        </VCard>
    </VDialog>
</template>
