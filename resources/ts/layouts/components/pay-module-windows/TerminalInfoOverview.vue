<script lang="ts" setup>
import BaseQuestionTooltip from '@/layouts/tooltips/BaseQuestionTooltip.vue'
import { comm_settle_types, ship_out_stats, under_sales_types } from '@/views/merchandises/pay-modules/useStore'
import { useStore } from '@/views/services/pay-gateways/useStore'
import type { PayModule } from '@/views/types'
import { isAbleModiy, salesLevels } from '@axios'

interface Props {
    item: PayModule,
}
const props = defineProps<Props>()
const { terminals } = useStore()

</script>
<template>
    <VCardItem>
        <!-- 장비 종류 -->
        <VRow v-if="isAbleModiy(props.item.id)">
            <VCol md="5" cols="5">장비 타입</VCol>
            <VCol md="7">
                <VSelect :menu-props="{ maxHeight: 400 }" v-model="props.item.terminal_id" :items="terminals"
                    prepend-inner-icon="ic-outline-send-to-mobile" item-title="name" item-value="id"
                    single-line />
            </VCol>
        </VRow>
        <VRow v-else>
            <VCol md="5" cols="5">
                <span class="font-weight-bold">장비 타입</span>
            </VCol>
            <VCol md="7">
                {{ terminals.find(obj => obj.id === props.item.terminal_id)?.name }}
            </VCol>
        </VRow>
        <!-- 👉 시리얼 번호 -->
        <VRow v-if="isAbleModiy(props.item.id)">
            <VCol md="5" cols="5">시리얼번호</VCol>
            <VCol md="7">
                <VTextField type="text" v-model="props.item.serial_num"
                        prepend-inner-icon="ic-twotone-stay-primary-portrait" placeholder="시리얼번호 입력"
                        persistent-placeholder />
            </VCol>
        </VRow>
        <VRow v-else>
            <VCol md="5" cols="5">
                <span class="font-weight-bold">시리얼번호</span>
            </VCol>
            <VCol md="7">
                {{ props.item.serial_num }}
            </VCol>
        </VRow>
        <!-- 통신비 -->
        <VRow v-if="isAbleModiy(props.item.id)">
            <VCol md="5" cols="5">통신비</VCol>
            <VCol md="7">
                <VTextField type="number" v-model="props.item.comm_settle_fee"
                        prepend-inner-icon="tabler-currency-won" placeholder="통신비 입력" persistent-placeholder />
            </VCol>
        </VRow>
        <VRow v-else>
            <VCol md="5" cols="5">
                <span class="font-weight-bold">통신비</span>
            </VCol>
            <VCol md="7">
                {{ props.item.comm_settle_fee ? props.item.comm_settle_fee.toLocaleString() : '' }}
            </VCol>
        </VRow>
        <VRow v-if="isAbleModiy(props.item.id)">
            <VCol md="5" cols="6">
                <BaseQuestionTooltip :location="'top'" :text="'통신비 정산타입'"
                        :content="'통신비, 통신비 정산타입, 개통일, 정산일, 정산주체가 설정되어있어야 적용됩니다.<br>ex)<br>통신비: 30,000<br>통신비 정산타입: 개통월 M+2부터 적용<br>개통일: 2023-09-25<br>정산일: 1일<br>정산주체: 가맹점<br><br>통신비 차감적용일: 2023-11-01, 2023-12-01, 2024-01-01 ...'"/>
            </VCol>
            <VCol md="7" cols="6">
                <VSelect :menu-props="{ maxHeight: 400 }" v-model="props.item.comm_settle_type"
                        :items="comm_settle_types" prepend-inner-icon="ic-baseline-calendar-today" label="정산타입"
                        item-title="title" item-value="id" persistent-hint single-line />
            </VCol>
        </VRow>
        <VRow v-else>
            <VCol md="5" cols="6">
                <span class="font-weight-bold">통신비 정산타입</span>
            </VCol>
            <VCol md="7" cols="6">
                {{ comm_settle_types.find(obj => obj.id === props.item.comm_settle_type)?.title }}
            </VCol>
        </VRow>
        <VDivider style="margin: 1em 0;" />
        <VRow v-if="isAbleModiy(props.item.id)">
            <VCol md="6" cols="6">매출미달 차감금</VCol>
            <VCol md="6">
                <VTextField type="number" v-model="props.item.under_sales_amt"
                        prepend-inner-icon="tabler-currency-won" placeholder="매출미달 차감금 입력" persistent-placeholder />
            </VCol>
        </VRow>
        <VRow v-else>
            <VCol md="6" cols="6">
                <span class="font-weight-bold">매출미달 차감금</span>
            </VCol>
            <VCol md="6">
                {{ props.item.under_sales_amt ? props.item.under_sales_amt.toLocaleString() : '' }}
            </VCol>
        </VRow>
        <VRow v-if="isAbleModiy(props.item.id)">
            <VCol md="6" cols="6">매출미달 하한금</VCol>
            <VCol md="6">
                <VTextField type="number" v-model="props.item.under_sales_limit"
                        prepend-inner-icon="tabler-currency-won" placeholder="매출미달 하한금 입력" persistent-placeholder
                        suffix="만원" />
            </VCol>
        </VRow>
        <VRow v-else>
            <VCol md="6" cols="6">
                <span class="font-weight-bold">매출미달 하한금</span>
            </VCol>
            <VCol md="6">
                {{ props.item.under_sales_limit ? (props.item.under_sales_limit * 10000).toLocaleString() : '' }}
            </VCol>
        </VRow>
        <VRow v-if="isAbleModiy(props.item.id)">
            <VCol md="6" cols="6">매출미달 적용기간</VCol>
            <VCol md="6">
                <VSelect :menu-props="{ maxHeight: 400 }" v-model="props.item.under_sales_type"
                        :items="under_sales_types" prepend-inner-icon="bi:calendar-range" label="적용기간 선택"
                        item-title="title" item-value="id" persistent-hint single-line />
            </VCol>
        </VRow>
        <VRow v-else>
            <VCol md="6" cols="6">
                <span class="font-weight-bold">매출미달 적용기간</span>
            </VCol>
            <VCol md="6">
                {{ under_sales_types.find(obj => obj.id === props.item.under_sales_type)?.title }}
            </VCol>
        </VRow>
        <VDivider style="margin: 1em 0;" />
        <VRow v-if="isAbleModiy(props.item.id)">
            <VCol md="5" cols="5">정산일</VCol>
            <VCol md="7">
                <VTextField v-model="props.item.comm_settle_day" suffix="일" />
            </VCol>
        </VRow>
        <VRow v-else>
            <VCol md="5" cols="5">
                <span class="font-weight-bold">정산일</span>
            </VCol>
            <VCol md="7">
                {{ props.item.comm_settle_day }}
            </VCol>
        </VRow>
        <VRow v-if="isAbleModiy(props.item.id)">
            <VCol md="5" cols="5">
                정산주체
            </VCol>
            <VCol md="7">
                <VSelect :menu-props="{ maxHeight: 400 }" v-model="props.item.comm_calc_level"
                        :items="[{ id: 10, title: '가맹점' }].concat(salesLevels())" prepend-inner-icon="ph:share-network"
                         item-title="title" item-value="id" persistent-hint single-line />
            </VCol>
        </VRow>
        <VRow v-else>
            <VCol md="5" cols="5">
                <span class="font-weight-bold">정산주체</span>
            </VCol>
            <VCol md="7">
                {{ salesLevels().find(obj => obj.id === props.item.comm_calc_level)?.title }}
            </VCol>
        </VRow>
        <VRow v-if="isAbleModiy(props.item.id)">
            <VCol md="5" cols="5">개통일</VCol>
            <VCol md="7">
                <VTextField type="date" v-model="props.item.begin_dt"
                        prepend-inner-icon="ic-baseline-calendar-today" single-line />
            </VCol>
        </VRow>
        <VRow v-else>
            <VCol md="5" cols="5">
                <span class="font-weight-bold">개통일</span>
            </VCol>
            <VCol md="7">
                {{ props.item.begin_dt }}
            </VCol>
        </VRow>
        <VRow v-if="isAbleModiy(props.item.id)">
            <VCol md="5" cols="5">출고일</VCol>
            <VCol md="7">
                    <VTextField type="date" v-model="props.item.ship_out_dt"
                        prepend-inner-icon="ic-baseline-calendar-today" single-line />
            </VCol>
        </VRow>
        <VRow v-else>
            <VCol md="5" cols="5">
                <span class="font-weight-bold">출고일</span>
            </VCol>
            <VCol md="7">
                {{ props.item.ship_out_dt }}
            </VCol>
        </VRow>
        <!-- 👉 출고상태 -->
        <VRow v-if="isAbleModiy(props.item.id)">
            <VCol md="5" cols="5">출고상태</VCol>
            <VCol md="7">
                    <VRadioGroup v-model="props.item.ship_out_stat" inline>
                        <VRadio v-for="(shipOutStat, key) in ship_out_stats" :key="key" :label="shipOutStat.title"
                            :value="shipOutStat.id" />
                    </VRadioGroup>
            </VCol>
        </VRow>
        <VRow v-else>
            <VCol md="5" cols="5">
                <span class="font-weight-bold">출고상태</span>
            </VCol>
            <VCol md="7">
                {{ ship_out_stats.find(obj => obj.id === props.item.ship_out_stat)?.title }}
            </VCol>
        </VRow>
    </VCardItem>
</template>
<style scoped>
:deep(.v-row) {
  align-items: center;
}
</style>
