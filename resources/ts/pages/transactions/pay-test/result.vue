<script setup lang="ts">
import PayResultOverview from '@/views/pay/PayResultOverview.vue'
import { payResult } from '@/views/pay/pay'

const { sale_slip, pgs, result_cd, result_msg } = payResult()
</script>
<template>
    <PayResultOverview :sale_slip="sale_slip" :pgs="pgs" :result_cd="result_cd" :result_msg="result_msg"/>
</template>
