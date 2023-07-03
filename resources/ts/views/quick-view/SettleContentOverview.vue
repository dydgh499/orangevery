<script setup lang="ts">
import type { MchtRecentTransaction } from '@/views/types'

interface Props {
    transaction: MchtRecentTransaction,
}

</script>
<template>
    <VCol>
        <div style="float: inline-end;">
            <span class="text-primary font-weight-bold">오늘 </span> 정산금액
        </div>
        <div>
            0원
        </div>
    </VCol>
    <VCol>
        <div>
            <div style="float: inline-end;">
                <span class="text-primary font-weight-bold">오늘 </span> 총 매출
            </div>
            <div>
                0원
            </div>
        </div>
    </VCol>
    <VCol>
        <div>
            <div style="float: inline-end;">
                <span class="text-primary font-weight-bold">오늘 </span> 승인/취소
            </div>
            <div>
                0/0 건
            </div>
        </div>
    </VCol>
</template>
