<script setup lang="ts">
import { useSalesFilterStore } from '@/views/salesforces/useStore';

const { mchts } = useSalesFilterStore()

const mcht_id = <any>(inject('mcht_id'))
const pmod_id = <any>(inject('pmod_id'))
const pay_modules = <any>(inject('pay_modules'))

</script>
<template>
    <VCardTitle><b>테스트 가맹점 정보</b></VCardTitle>
    <VCol>
        <VRow>
            <VCol md="6" cols="12">
                <VRow no-gutters>
                    <VCol cols="4" :md="3">
                        <label>가맹점</label>
                    </VCol>
                    <VCol cols="8" :md="9">
                        <VAutocomplete :menu-props="{ maxHeight: 400 }" v-model="mcht_id" :items="mchts"
                            variant="underlined"
                            prepend-icon="tabler-building-store" item-title="mcht_name" item-value="id" single-line
                            placeholder="테스트 가맹점 선택" />
                    </VCol>
                </VRow>
            </VCol>
            <VCol md="6" cols="12">
                <VRow no-gutters>
                    <VCol cols="4" :md="3">
                        <label>결제모듈</label>
                    </VCol>
                    <VCol cols="8" :md="9">
                        <VSelect :menu-props="{ maxHeight: 400 }" v-model="pmod_id" :items="pay_modules"
                            variant="underlined"
                            prepend-icon="ic-outline-send-to-mobile" item-title="note" item-value="id" single-line
                            placeholder="테스트 결제모듈 선택" />
                    </VCol>
                </VRow>
            </VCol>
        </VRow>
    </VCol>
</template>
<style scoped>
:deep(.v-row) {
  align-items: center;
}
</style>
