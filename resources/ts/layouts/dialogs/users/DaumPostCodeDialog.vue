<script setup lang="ts">
import { VueDaumPostcode, VueDaumPostcodeCompleteResult } from 'vue-daum-postcode';

const visible = ref(false)
const result = ref<VueDaumPostcodeCompleteResult | null>(null)
let resolveCallback: (address: string) => void;

const show = async () => {
    visible.value = true
    return new Promise<string>((resolve) => {
        resolveCallback = resolve;
    });
}

const onCancel = () => {
    visible.value = false
    resolveCallback('')
}

const onComplete = (newResult: VueDaumPostcodeCompleteResult) => {
  result.value = newResult
  visible.value = false
  resolveCallback(result.value.address)
}

defineExpose({
    show
});
</script>
<template>
    <VDialog v-model="visible" persistent max-width="800">
        <DialogCloseBtn @click="onCancel()" />
        <br>
        <VCard>
            <VueDaumPostcode :options="[]" @complete="onComplete"/>
        </VCard>
    </VDialog>
</template>
