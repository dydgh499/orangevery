<script setup lang="ts">
import { token_expire_time } from '@axios';


const remaining_time = ref(<string>("00:00:00"))

const updateRemainingTime = () => {
    const expire = new Date(token_expire_time.value)
    const now = new Date()
    const diff = expire.getTime() - now.getTime()

    if(!Number.isNaN(diff))
    {
        if(diff < 0) {
            remaining_time.value = "00:00:00";
        } else {
            const hours = Math.floor(diff / (1000 * 60 * 60))
            const minutes = Math.floor((diff % (1000 * 60 * 60)) / (1000 * 60))
            const seconds = Math.floor((diff % (1000 * 60)) / 1000)

            const formatted_hours = hours.toString().padStart(2, '0')
            const formatted_mins = minutes.toString().padStart(2, '0')
            const formatted_secs = seconds.toString().padStart(2, '0')

            remaining_time.value = `${formatted_hours}:${formatted_mins}:${formatted_secs}`
        }
    }
};
const getRemainTimeColor = computed(() => {
    const expire = new Date(token_expire_time.value);
    const now = new Date();
    const diff = expire.getTime() - now.getTime();
    if(diff < 0)
        return 'text-error'
    else {
        const minutes = Math.floor(diff / (1000 * 60))
        if(minutes < 5)
            return 'text-error'
        else
            return 'text-primary'
    }
})

const intervalId = setInterval(updateRemainingTime, 1000);
onUnmounted(() => {
    clearInterval(intervalId);
});
</script>
<template>
    <span style="margin-right: 1em;">
        <span>세션 유지: </span>
        <span :class="getRemainTimeColor">{{ remaining_time }}</span>
    </span>
</template>
