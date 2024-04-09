<script setup lang="ts">
import BaseQuestionTooltip from '@/layouts/tooltips/BaseQuestionTooltip.vue';
import { SettlesHistory } from '@/views/types';

const visible = ref(false)
const amount = ref(0)
const settle_history = ref(<SettlesHistory>({}))

let resolveCallback: (amount: number) => void;

const show = (history: SettlesHistory): Promise<number> => {
    amount.value = 0
    settle_history.value = history
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
    <VDialog v-model="visible" max-width="400">
        <!-- Dialog close btn -->
        <DialogCloseBtn @click="visible = false" />
        <!-- Dialog Content -->
        <VCard title="추가차감할 금액 입력">
            <VCardText>
                <VForm ref="vForm">
                    <VCol cols="12">
                        <VRow no-gutters style="align-items: center;">
                            <VCol cols="12" md="5">                                        
                                <BaseQuestionTooltip :location="'top'" :text="'추가차감금'" :content="'차감이 아닌 추가금 설정을 하시러면 금액 앞에 -(마이너스 기호)를 입력 후 차감버튼을 클릭해주세요.'">
                                </BaseQuestionTooltip>
                            </VCol>
                            <VCol cols="12" md="7">
                                <VTextField v-model="amount" type="number" suffix="￦" />
                            </VCol>
                        </VRow>
                    </VCol>
                    <VCol cols="12">
                        <VRow no-gutters style="align-items: center;">
                            <VCol cols="12" style="text-align: end;">
                                정산액: <b>{{ (settle_history.settle_amount - amount).toLocaleString() }} 원</b>
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
