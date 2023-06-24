<script setup lang="ts">

const head = <any>(inject('head'))
const visible = ref(false)
const fullColspans = head.getFullColspans()
const fullColspanSize = fullColspans.reduce((acc: number, cur: number) => acc + cur, 0)
const show = () => {
    visible.value = true;
}
const getStartIdx = (idx: number) => {
    return fullColspans.slice(0, idx).reduce((sum: number, value: number) => sum + value, 0);
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
        <VCard title="검색 필터">
            <VCardText>
                <div>
                    <template v-for="(leng, index) in fullColspans" :key="key">
                        <span>{{ head.main_headers[index] }}</span>
                        <span>
                            <VCol cols="12">
                                <VRow no-gutters>
                                    <template v-for="(_header, key, _index) in head.flat_headers" :key="_index">
                                        <template v-if="_index >= getStartIdx(index) && _index < getStartIdx(index) + leng">
                                            <VCol :cols="($vuetify.display.smAndDown ? 6 : 4)">
                                                <VCheckbox v-model="_header.hidden" :label="_header.ko"
                                                    true-icon="tabler-circle-x" false-icon="tabler-eye-check"
                                                    color="primary" />
                                            </VCol>
                                        </template>
                                    </template>
                                </VRow>
                            </VCol>
                        </span>
                    </template>
                </div>
                <div>
                    <span>
                        <VCol cols="12">
                            <VRow no-gutters>
                                <template v-for="(_header, key, _index) in head.flat_headers" :key="_index">
                                    <template v-if="_index >= fullColspanSize">
                                        <VCol :cols="($vuetify.display.smAndDown ? 6 : 4)">
                                            <VCheckbox v-model="_header.hidden" :label="_header.ko"
                                                true-icon="tabler-circle-x" false-icon="tabler-eye-check"
                                                color="primary" />
                                        </VCol>
                                    </template>
                                </template>
                            </VRow>
                        </VCol>
                    </span>
                </div>
            </VCardText>
        </VCard>
    </VDialog>
</template>
