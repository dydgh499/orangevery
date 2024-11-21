<script setup lang="ts">
import { getUserLevel } from '@axios';


const store = <any>(inject('store'))
const settle = <any>(inject('settle'))
const partSettle = <any>(inject('partSettle'))

</script>
<template>
    <section style="display: inline-flex; align-items: center;">
        <VBtn prepend-icon="tabler-calculator" @click="partSettle()" v-if="getUserLevel() >= 35" size="small">
            부분정산
        </VBtn>
        <div v-if="store.params.level === 10" style="padding: 0 1em;">
            <VSwitch hide-details :false-value=0 :true-value=1 v-model="store.params.use_realtime_deposit"
                label="실시간 포함" color="primary"
                @update:modelValue="[store.updateQueryString({ use_realtime_deposit: store.params.use_realtime_deposit })]" />
            <VSwitch hide-details :false-value=0 :true-value=1 v-model="store.params.only_cancel" label="취소 매출 조회"
                color="error"
                @update:modelValue="store.updateQueryString({ only_cancel: store.params.only_cancel })" />
        </div>
        <div v-else  style="padding: 0 1em;">
            <VSwitch hide-details :false-value=0 :true-value=1 v-model="store.params.is_base_trx" label="매출일 기준 조회" color="primary" @update:modelValue="[store.updateQueryString({is_base_trx: store.params.is_base_trx})]"/>                
            <VSwitch hide-details :false-value=0 :true-value=1 v-model="store.params.only_cancel" label="취소 매출 조회"
                    color="error"
                    @update:modelValue="store.updateQueryString({ only_cancel: store.params.only_cancel })" />
        </div>

        <div style="display: flex;">
            <table>
                <tr>
                    <th>매출액 합계</th>
                    <td><span>{{ settle.total_amount.toLocaleString() }}</span> &#8361;</td>
                </tr>
                <tr>
                    <th>승인액 합계</th>
                    <td><span>{{ settle.appr_amount.toLocaleString() }}</span> &#8361;</td>
                </tr>
                <tr>
                    <th>취소액 합계</th>
                    <td><span>{{ settle.cxl_amount.toLocaleString() }}</span> &#8361;</td>
                </tr>
            </table>
            <table>
                <tr>
                    <th>정산액 합계</th>
                    <td><span>{{ settle.settle_amount.toLocaleString() }}</span> &#8361;</td>
                </tr>
                <tr>
                    <th>거래 수수료</th>
                    <td><span>{{ settle.trx_amount.toLocaleString() }}</span> &#8361;</td>
                </tr>
                <tr v-if="store.params.level === 10">
                    <th>입금 수수료</th>
                    <td><span>{{ settle.settle_fee.toLocaleString() }}</span> &#8361;</td>
                </tr>
                <tr v-else>
                    <th><br></th>
                    <td><span></span></td>
                </tr>
            </table>
        </div>
    </section>
</template>
