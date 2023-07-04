<script setup lang="ts">
import type { MchtRecentTransaction } from '@/views/types'

interface Props {
    transaction: MchtRecentTransaction,
    date:string,
}
const props = defineProps<Props>()

const displayDate = () => {
    const currentDate = new Date()
    const currentYear = currentDate.getFullYear().toString()
    const currentMonth = (currentDate.getMonth() + 1).toString().padStart(2, '0')
    const today = currentDate.getDate().toString().padStart(2, '0')

    const inputDateParts = props.date.split('-');
    const inputYear = inputDateParts[0]
    const inputMonth = inputDateParts[1]

    const monthNames = ["", "1월", "2월", "3월", "4월", "5월", "6월", "7월", "8월", "9월", "10월", "11월", "12월"];
    let date = ''
    let style = ''

    if (props.date === `${currentYear}-${currentMonth}-${today}`) {
        date = '오늘'
        style = 'color:#06C4AE !important;'
    }
    else if (inputYear === currentYear && inputDateParts.length === 2) {
        date    = monthNames[parseInt(inputMonth, 10)]
        style   = currentMonth == inputMonth ? '' : 'color:#DF3E2A !important;';
    } else {
        date    = props.date
        style   = 'color:#000000;'
    }

    return {
        date, style
    }
};
const { date, style } = displayDate()
</script>
<template>
    <VCol>
        <div style="text-align: end;">
            <span class="text-primary" :style="style">{{ date }} </span> 정산금액
        </div>
        <div style="font-weight: bold;text-align: end;">
            <span>
                {{ props.transaction.profit.toLocaleString() }}
            </span>
            <span> 원</span>
        </div>
    </VCol>
    <VCol>
        <div>
            <div style="text-align: end;">
                <span class="text-primary" :style="style">{{ date }} </span> 총 매출
            </div>
            <div style="font-weight: bold;text-align: end;">                 
                <span>
                    {{ props.transaction.amount.toLocaleString() }}
                </span>
                <span> 원</span>
            </div>
        </div>
    </VCol>
    <VCol>
        <div>
            <div style="text-align: end;">
                <span class="text-primary" :style="style">{{ date }} </span> 승인/취소
            </div>
            <div style="font-weight: bold;text-align: end;">
                <span>
                    {{ props.transaction.appr.count.toLocaleString() }}/{{ props.transaction.cxl.count.toLocaleString() }}
                </span>
                <span> 건</span>
            </div>
        </div>
    </VCol>
    {{ props.date }}
</template>
