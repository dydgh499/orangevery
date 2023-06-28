<script lang="ts" setup>
import {axios} from '@axios'

const visible = ref(false)
axios.interceptors.request.use((config) => {
    visible.value = true
    return config;
    }, (error) => {
    visible.value = false
    return Promise.reject(error);
});
axios.interceptors.response.use((response) => {
    visible.value = false
    return response;
    }, (error) => {
    visible.value = false
    return Promise.reject(error);
});
</script>
<template>
    <VDialog
        v-model="visible"
        width="300"
        >
        <VCard
        color="primary"
        width="300"
        >
        <VCardText class="pt-3">
            잠시만 기다려주세요 ...
            <VProgressLinear
            indeterminate
            class="mb-0"
            />
        </VCardText>
        </VCard>
    </VDialog>
</template>
