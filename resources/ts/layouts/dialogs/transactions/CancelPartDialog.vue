<script setup lang="ts">
import { inputFormater } from '@/@core/utils/formatters';
import BaseQuestionTooltip from '@/layouts/tooltips/BaseQuestionTooltip.vue';

const visible = ref(false)
const {
    amount_format,
    amount,
    formatAmount,
} = inputFormater()

let resolveCallback: (amount: number) => void;

const show = (_amount: number): Promise<number> => {
    amount_format.value = _amount.toString()
    amount.value = _amount
    visible.value = true

    return new Promise<number>((resolve) => {
        resolveCallback = resolve;
    });
}

const input = (a: number) => {
    visible.value = false
    resolveCallback(a)
}

const handleEvent = (event: KeyboardEvent) => {
    event.preventDefault()
    input(amount.value)
}

defineExpose({
    show
});
</script>
<template>
    <VDialog v-model="visible" max-width="400">
        <!-- Dialog close btn -->
        <DialogCloseBtn @click="visible = false" />
        <!-- Dialog Content -->
        <VCard title="부분취소">
            <VCardText>
                <VForm ref="vForm">
                    <VCol cols="12">
                        <VRow no-gutters>
                            <VCol cols="12" md="4">                                        
                                <BaseQuestionTooltip :location="'top'" :text="'취소금액'" :content="'전액 입력시, 전액취소됩니다.'"/>
                            </VCol>
                            <VCol cols="12" md="8">
                                <VTextField 
                                    inputmode="numeric"
                                    v-model="amount_format"
                                    suffix="원" 
                                    @input="formatAmount"
                                    variant="underlined" placeholder="취소금액을 입력해주세요"
                                    prepend-icon="ic:outline-price-change"
                                    @keydown.enter="handleEvent"
                                />
                            </VCol>
                        </VRow>
                    </VCol>
                </VForm>
            </VCardText>
            <VCardText class="d-flex justify-end gap-3 flex-wrap">
                <VBtn color="secondary" variant="tonal" @click="input(0)">
                    취소
                </VBtn>
                <VBtn @click="input(amount)">
                    확인
                </VBtn>
            </VCardText>
        </VCard>
    </VDialog>
</template>
<style scoped>
:deep(.v-row) {
  align-items: center;
}
</style>
