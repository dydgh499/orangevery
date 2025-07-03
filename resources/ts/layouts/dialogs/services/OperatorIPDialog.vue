<script setup lang="ts">
import OperatorIpCard from '@/views/services/operator-ips/OperatorIpCard.vue';
import { OperatorIp } from '@/views/types';
import { axios } from '@axios';
import { timerV1 } from '@core/utils/timer';


const { countdown_time, countdownTimer, restartTimer } = timerV1(300)
const operator_ips = ref(<OperatorIp[]>([]))
const visible = ref(false)
const token = ref('')

const show = async (_token: string) => {
    token.value = _token
    const r = await axios.get('/api/v1/manager/services/operator-ips', {
        params: {
            page: 1,
            page_size: 999,
            token: token.value,
        }
    })
    operator_ips.value = r.data.content
    visible.value = true
    restartTimer()
}

provide('token', token)

defineExpose({
    show
});
</script>
<template>
    <section>
        <VDialog v-model="visible" max-width="550">
            <DialogCloseBtn @click="visible = false" />
            <VCard>
                <VCardItem>
                    <OperatorIpCard :key="operator_ips.length" :operator_ips="operator_ips" :countdown_timer="countdownTimer"/>
                </VCardItem>
                <br>
            </VCard>
        </VDialog>
    </section>
</template>
