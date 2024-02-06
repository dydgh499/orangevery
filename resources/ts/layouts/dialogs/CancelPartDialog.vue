<script setup lang="ts">
import BaseQuestionTooltip from '@/layouts/tooltips/BaseQuestionTooltip.vue'

const visible = ref(false)
const amount = ref(0)

let resolveCallback: (amount: number) => void;

const show = (_amount: number): Promise<number> => {
    amount.value = _amount
    visible.value = true

    return new Promise<number>((resolve) => {
        resolveCallback = resolve;
    });
}

const input = (amount: number) => {
    visible.value = false
    resolveCallback(amount)
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
        <VCard title="부분취소">
            <VCardText>
                <VForm ref="vForm">
                    <VCol cols="12">
                        <VRow no-gutters>
                            <VCol cols="12" md="4">                                        
                                <BaseQuestionTooltip :location="'top'" :text="'취소금액'" :content="'전액 입력시, 전액취소됩니다.'">
                                </BaseQuestionTooltip>
                            </VCol>
                            <VCol cols="12" md="8">
                                <VTextField v-model="amount" type="number" suffix="￦" placeholder="취소금액을 입력해주세요"
                                    prepend-inner-icon="ic:outline-price-change" />
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
