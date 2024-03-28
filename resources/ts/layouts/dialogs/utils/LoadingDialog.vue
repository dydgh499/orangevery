<script lang="ts" setup>
import { axios } from '@axios'
import { token_expire_time } from '@axios'

const active_count = ref(0)
const visible = ref(false)

axios.interceptors.request.use((config) => {
    active_count.value++
    return config;
}, (error) => {
    active_count.value++
    return Promise.reject(error);
});
axios.interceptors.response.use((response) => {
    active_count.value--
    if(response.headers['token-expire-time']) {
        token_expire_time.value = response.headers['token-expire-time']
        localStorage.setItem('token-expire-time', token_expire_time.value)
    }
    return response;
});

const show = (_visible: boolean) => {
    visible.value = _visible
}

watchEffect(() => {
    visible.value = active_count.value ? true : false
})

defineExpose({
    show
});
</script>
<template>
    <VDialog v-model="visible" width="300">
        <VCard color="primary" width="300">
            <VCardText class="pt-3">
                잠시만 기다려주세요 ...
                <VProgressLinear indeterminate class="mb-0" />
            </VCardText>
        </VCard>
    </VDialog>
</template>
