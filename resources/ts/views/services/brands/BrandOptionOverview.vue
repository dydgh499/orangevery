<script setup lang="ts">
import BaseQuestionTooltip from '@/layouts/tooltips/BaseQuestionTooltip.vue';
import CreateHalfVCol from '@/layouts/utils/CreateHalfVCol.vue';
import type { AuthOption, FreeOption, PaidOption } from '@/views/types';
import { getUserLevel } from '@axios';
interface Props {
    item: {
        free: FreeOption,
        paid: PaidOption,
        auth: AuthOption,
    },
}
const props = defineProps<Props>()
</script>

<template>
    
</template>
