<script setup lang="ts">
import { token_expire_time } from '@axios';
import { timerV2 } from '@core/utils/timer';

const { remaining_time, getRemainTimeColor} = timerV2(token_expire_time.value)

</script>
<template>
    <span :style="$vuetify.display.smAndDown ? '' : 'margin-right: 1em;'">
        <span>세션 유지: </span>
        <span :class="getRemainTimeColor">{{ remaining_time }}</span>
    </span>
</template>
