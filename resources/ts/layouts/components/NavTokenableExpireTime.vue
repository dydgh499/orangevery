<script setup lang="ts">
import { token_expire_time } from '@axios';
import { hourTimer } from '@core/utils/timer';

const {remaining_time, expire_time, getRemainTimeColor, updateRemainingTime} = hourTimer()
expire_time.value = token_expire_time.value
const intervalId = setInterval(updateRemainingTime, 1003);

onUnmounted(() => {
    clearInterval(intervalId);
});
</script>
<template>
    <span :style="$vuetify.display.smAndDown ? '' : 'margin-right: 1em;'">
        <span>세션 유지: </span>
        <span :class="getRemainTimeColor">{{ remaining_time }}</span>
    </span>
</template>
