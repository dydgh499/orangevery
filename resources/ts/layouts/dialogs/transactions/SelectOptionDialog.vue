<script setup lang="ts">
import { OptionGroup } from '@/views/types'

const visible = ref(false)
const option_groups = ref(<OptionGroup[]>({}))

const show = (_option_group: string) => {
    option_groups.value = <OptionGroup[]>(JSON.parse(_option_group))
    visible.value = true
}

defineExpose({
    show
});
</script>
<template>
    <VDialog v-model="visible" max-width="600">
        <!-- Dialog close btn -->
        <DialogCloseBtn @click="visible = false" />
        <!-- Dialog Content -->
        <VCard title="선택옵션 목록">
            <VCardText>
                <VTable class="text-no-wrap" style="width: 100%;">
                    <thead>
                        <tr>
                            <th class='list-square'>옵션명</th>
                            <th class='list-square'>옵션가격</th>
                            <th class='list-square'>선택수량</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="(option, key) in option_groups" :key="key">
                            <td class='list-square'>{{ option.option_name }}</td>
                            <td class='list-square'>{{ option.option_price.toLocaleString() }}원</td>
                            <td class='list-square'>{{ option.count ?? 1 }}</td>
                        </tr>
                    </tbody>
                    <tfoot v-if="!Boolean(option_groups.length)">
                        <tr>
                            <td colspan="3" class='list-square' style="border: 0;">
                                선택한 옵션이 존재하지 않습니다.
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
