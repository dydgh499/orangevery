<script setup lang="ts">
import type { MchtRecentTransaction } from '@/views/types'

interface Props {
    transaction: MchtRecentTransaction,
    date: string,
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
        style = 'success'
    }
    else if (inputYear === currentYear && inputDateParts.length === 2) {
        date = monthNames[parseInt(inputMonth, 10)]
        style = currentMonth == inputMonth ? 'warning' : 'primary';
    } else {
        date = props.date
        style = 'color:#000000;'
    }

    return {
        date, style
    }
};
const { date, style } = displayDate()
</script>
<template>
    <VCol class="d-flex justify-space-between small-font">
        <div>
            <div class="small-font">
                <span :class="'text-' + style">{{ date }} </span> 정산금액
            </div>
            <div style="font-weight: bold;">
                <span>
                    {{ props.transaction.profit.toLocaleString() }}
                </span>
                <span class="small-font" style="font-weight: 500;"> 원</span>
            </div>
        </div>
        <VAvatar rounded variant="tonal" :color="style" icon="ic-outline-payments" />
    </VCol>
    <VCol style="padding-top: 0;" class="d-flex justify-space-between small-font">
        <div>
            <div class="small-font">
                <span :class="'text-' + style">{{ date }} </span> 총 매출
            </div>
            <div style="font-weight: bold;">
                <span>
                    {{ props.transaction.amount.toLocaleString() }}
                </span>
                <span class="small-font" style="font-weight: 500;"> 원</span>
            </div>
        </div>
    </VCol>
    <VCol style="padding-top: 0;" class="d-flex justify-space-between small-font">
        <div>
            <div class="small-font">
                <span :class="'text-' + style">{{ date }} </span> 승인/취소
            </div>
            <div style="font-weight: bold;">
                <span>
                    {{ props.transaction.appr.count.toLocaleString() }}/{{ props.transaction.cxl.count.toLocaleString()
                    }}
                </span>
                <span class="small-font" style="font-weight: 500;"> 건</span>
            </div>
        </div>
        <span style=" margin-top: auto; font-weight: 600;" class="small-font">{{ props.date }}</span>
    </VCol>
</template>
<style>
.small-font {
  font-size: 0.9em;
}
</style>
