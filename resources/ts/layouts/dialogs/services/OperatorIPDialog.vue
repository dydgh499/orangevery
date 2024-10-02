<script setup lang="ts">
import OperatorIpCard from '@/views/services/operator-ips/OperatorIpCard.vue';
import { useOperatorIpStore } from '@/views/services/operator-ips/useStore';

const { operator_ips } = useOperatorIpStore()
const visible = ref(false)

const show = () => {
    visible.value = true
}
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
                    <OperatorIpCard :key="operator_ips.length"/>
                </VCardItem>
                <br>
            </VCard>
        </VDialog>
    </section>
</template>
