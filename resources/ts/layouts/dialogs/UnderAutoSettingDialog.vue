<script lang="ts" setup>
import type { UnderAutoSetting } from '@/views/types'

const visible = ref(false)
const under_auto_settings = ref(<UnderAutoSetting[]>([]))

let resolveCallback: (idx: number) => void;

const show = (_under_auto_settings: UnderAutoSetting[]): Promise<number> => {
    under_auto_settings.value = _under_auto_settings
    visible.value = true

    return new Promise<number>((resolve) => {
        resolveCallback = resolve;
    });
}

const selected = (idx: number) => {
    visible.value = false
    resolveCallback(idx)
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
                <template >
                    <VTable style="width: 100%;margin-bottom: 1em;text-align: center;">
                        <thead>
                            <tr>
                                <th scope="col" style="text-align: center;">No.</th>
                                <th scope="col" style="text-align: center;">별칭</th>
                                <th scope="col" style="text-align: center;">수수료</th>
                                <th scope="col" style="text-align: center;"></th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="(under_auto_setting, key) in under_auto_settings" :key="key">
                                <td scope="col" style="text-align: center;">{{ key+1 }}</td>
                                <td scope="col" style="text-align: center;">{{ under_auto_setting.note }}</td>
                                <td scope="col" style="text-align: center;">{{ under_auto_setting.sales_fee }}</td>
                                <td scope="col" style="text-align: center;">
                                    <VBtn @click="selected(key)">
                                        <span style="font-weight: bold;">선택하기</span>
                                    </VBtn>
                                </td>
                            </tr>
                        </tbody>
                    </VTable>
                    <br>
                </template>
            </VCardText>
        </VCard>
    </VDialog>
</template>
<style scoped>
:deep(.v-table__wrapper) {
  block-size: auto !important;
}
</style>

