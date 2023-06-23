<script setup lang="ts">

const head = <any>(inject('head'))
const visible = ref(false)
const show = () => {
    visible.value = true;
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
                <VRow>
                    <VCol>
                        <VCheckbox v-for="(header, index) in head.flat_headers" :key="index" v-model="header.hidden"
                            :label="header.ko" true-icon="tabler-circle-x" false-icon="tabler-eye-check" color="primary" />
                    </VCol>
                </VRow>
            </VCardText>
        </VCard>
    </VDialog>
</template>
