<script lang="ts" setup>
import BaseQuestionTooltip from '@/layouts/tooltips/BaseQuestionTooltip.vue';
import { comm_settle_types, ship_out_stats, under_sales_types } from '@/views/merchandises/pay-modules/useStore';
import { StatusColorSetter } from '@/views/searcher';
import { useStore } from '@/views/services/pay-gateways/useStore';
import type { PayModule } from '@/views/types';
import { isAbleModiyV2, salesLevels } from '@axios';

interface Props {
    item: PayModule,
}
const props = defineProps<Props>()
const { terminals } = useStore()

</script>
<template>
    <VCardItem>
        <VCardSubtitle>
            <VChip variant="outlined">단말기 정보</VChip>
        </VCardSubtitle>
        <br>
        <VRow v-if="isAbleModiyV2(props.item, 'merchandises/pay-modules')">
            <VCol md="6" cols="12">
                <VSelect :menu-props="{ maxHeight: 400 }" v-model="props.item.terminal_id" :items="terminals"
                    prepend-inner-icon="ic-outline-send-to-mobile" item-title="name" item-value="id"
                    label="장비 타입" />
            </VCol>
            <VCol md="6">                
                <VTextField type="text" v-model="props.item.serial_num" placeholder="시리얼번호 입력"
                        prepend-inner-icon="ic-twotone-stay-primary-portrait" label="시리얼번호"
                        persistent-placeholder />
            </VCol>
        </VRow>
        <VRow v-else>
            <VCol md="5" cols="6">
                <span class="font-weight-bold">장비 타입</span>
            </VCol>
            <VCol md="7" cols="6">
                {{ terminals.find(obj => obj.id === props.item.terminal_id)?.name }}
            </VCol>
            <VCol md="5" cols="6">
                <span class="font-weight-bold">시리얼번호</span>
            </VCol>
            <VCol md="7" cols="6">
                {{ props.item.serial_num }}
            </VCol>
        </VRow>
        <VRow v-if="isAbleModiyV2(props.item, 'merchandises/pay-modules')">
            <VCol md="6" cols="12">
                <AppDateTimePicker v-model="props.item.ship_out_dt" prepend-inner-icon="ic-baseline-calendar-today" label="출고일"/>
            </VCol>
            <VCol md="6" cols="12">
                <VSelect :menu-props="{ maxHeight: 400 }" v-model="props.item.ship_out_stat"
                    :items="ship_out_stats" prepend-inner-icon="tabler:truck-delivery"
                        item-title="title" item-value="id" label="출고상태" />
            </VCol>
        </VRow>
        <VRow v-else>
            <VCol md="5" cols="6">
                <span class="font-weight-bold">출고일</span>
            </VCol>
            <VCol md="7" cols="6">
                {{ props.item.ship_out_dt }}
            </VCol>
            <VCol md="5" cols="6">
                <span class="font-weight-bold">출고상태</span>
            </VCol>
            <VCol md="7" cols="6">
                <VChip :color="StatusColorSetter().getSelectIdColor(props.item.ship_out_stat || 0)">
                    {{ ship_out_stats.find(obj => obj.id === props.item.ship_out_stat)?.title }}
                </VChip>
            </VCol>
        </VRow>

        
        <VDivider style="margin: 1em 0;" />
        <VCardSubtitle>
            <VChip variant="outlined">추가정산 정보</VChip>
            <BaseQuestionTooltip :location="'top'" :text="''" v-if="isAbleModiyV2(props.item, 'merchandises/pay-modules')"
                    :content="`통신비와 매출미달 차감금의 추가정산액에 대한 설정입니다.<br>
                    추가정산일이 영업일이 아닌경우 추가정산액은 다음정산일에 반영됩니다.
            `"/>
        </VCardSubtitle>
        <br>
        <VRow v-if="isAbleModiyV2(props.item, 'merchandises/pay-modules')">
            <VCol md="6" cols="12">
                <VTextField v-model="props.item.comm_settle_day" suffix="일" label="추가정산일"/>
            </VCol>
            <VCol md="6">
                <VSelect :menu-props="{ maxHeight: 400 }" v-model="props.item.comm_calc_level"
                    :items="[{ id: 10, title: '가맹점' }].concat(salesLevels())" prepend-inner-icon="ph:share-network"
                        item-title="title" item-value="id" label="추가정산액 부가대상" />
            </VCol>
        </VRow>
        <VRow v-else>
            <VCol md="5" cols="6">
                <span class="font-weight-bold">추가정산일</span>
            </VCol>
            <VCol md="7" cols="6">
                {{ props.item.comm_settle_day }}일
            </VCol>
            <VCol md="5" cols="6">
                <span class="font-weight-bold">추가정산액 부가대상</span>
            </VCol>
            <VCol md="7" cols="6">
                {{ salesLevels().find(obj => obj.id === props.item.comm_calc_level)?.title }}
            </VCol>

        </VRow>
        <VDivider style="margin: 1em 0;" />
        <VCardSubtitle>
            <VChip variant="outlined">통신비 정보</VChip>
            <BaseQuestionTooltip :location="'top'" :text="''" v-if="isAbleModiyV2(props.item, 'merchandises/pay-modules')"
                        :content="`추가정산정보가 설정되어있어야 적용됩니다.<br>
                        ex)<br>
                        통신비: 30,000<br>
                        추가정산 적용월: 개통월 M+2부터 적용<br>
                        개통일: 2023-09-25<br><br>
                        통신비 차감적용일: 2023-11-01, 2023-12-01, 2024-01-01 ...`"/>
        </VCardSubtitle>
        <br>
        <VRow v-if="isAbleModiyV2(props.item, 'merchandises/pay-modules')">
            <VCol md="6" cols="12">
                <VTextField type="number" v-model="props.item.comm_settle_fee" label="통신비"
                        prepend-inner-icon="tabler-currency-won" placeholder="통신비 입력" persistent-placeholder />
            </VCol>
            <VCol md="6">
                <VSelect :menu-props="{ maxHeight: 400 }" v-model="props.item.comm_settle_type"
                        :items="comm_settle_types" prepend-inner-icon="ic-baseline-calendar-today" label="추가정산 적용월"
                        item-title="title" item-value="id" />
            </VCol>
        </VRow>
        <VRow v-else>
            <VCol md="5" cols="6">
                <span class="font-weight-bold">통신비</span>
            </VCol>
            <VCol md="7" cols="6">
                {{ props.item.comm_settle_fee ? props.item.comm_settle_fee.toLocaleString() : '0' }}원
            </VCol>
            <VCol md="5" cols="6">
                <span class="font-weight-bold">통신비 정산타입</span>
            </VCol>
            <VCol md="7" cols="6">
                <VChip :color="StatusColorSetter().getSelectIdColor(props.item.comm_settle_type || 0)">
                    {{ comm_settle_types.find(obj => obj.id === props.item.comm_settle_type)?.title }}
                </VChip>
            </VCol>
        </VRow>
        <VRow v-if="isAbleModiyV2(props.item, 'merchandises/pay-modules')">
            <VCol md="6" cols="12">
                <AppDateTimePicker v-model="props.item.begin_dt" prepend-inner-icon="ic-baseline-calendar-today" label="개통일"/>
            </VCol>
        </VRow>
        <VRow v-else>
            <VCol md="5" cols="6">
                <span class="font-weight-bold">개통일</span>
            </VCol>
            <VCol md="7" cols="6">
                {{ props.item.begin_dt }}
            </VCol>
        </VRow>
        <VDivider style="margin: 1em 0;" />
        <VCardSubtitle>
            <VChip variant="outlined">매출미달 차감금</VChip>
            <BaseQuestionTooltip :location="'top'" :text="''" v-if="isAbleModiyV2(props.item, 'merchandises/pay-modules')"
                        :content="`추가정산정보가 설정되어있어야 적용됩니다.<br>
                        ex)<br>
                        매출미달 차감액: 30,000<br>
                        매출미달 하한금: 10,000만원<br>
                        매출미달 적용기간: 작월 1일 ~ 작월 말일<br>
                        작월 매출액: 9,000만원<br><br>
                        추가정산액 정산시기에 30,000원이 차감된 후 정산`"/>
        </VCardSubtitle>
        <br>
        <VRow v-if="isAbleModiyV2(props.item, 'merchandises/pay-modules')">
            <VCol md="6" cols="12">
                <VTextField type="number" v-model="props.item.under_sales_amt"
                    prepend-inner-icon="tabler-currency-won" label="매출미달 차감액" />
            </VCol>
            <VCol md="6">
                <VTextField type="number" v-model="props.item.under_sales_limit"
                    prepend-inner-icon="tabler-currency-won" label="매출미달 하한금" persistent-placeholder
                    suffix="만원" />
            </VCol>
        </VRow>
        <VRow v-else>
            <VCol md="5" cols="6">
                <span class="font-weight-bold">매출미달 차감액</span>
            </VCol>
            <VCol md="7" cols="6">
                {{ props.item.under_sales_amt ? props.item.under_sales_amt.toLocaleString() : '0' }}원
            </VCol>
            <VCol md="5" cols="6">
                <span class="font-weight-bold">매출미달 하한금</span>
            </VCol>
            <VCol md="7" cols="6">
                {{ props.item.under_sales_limit ? (props.item.under_sales_limit * 10000).toLocaleString() : '0' }}원
            </VCol>
        </VRow>
        <VRow v-if="isAbleModiyV2(props.item, 'merchandises/pay-modules')">
            <VCol md="6">
                <VSelect :menu-props="{ maxHeight: 400 }" v-model="props.item.under_sales_type"
                        :items="under_sales_types" prepend-inner-icon="bi:calendar-range" label="매출미달 적용기간"
                        item-title="title" item-value="id" />
            </VCol>
        </VRow>
        <VRow v-else>
            <VCol md="5" cols="6">
                <span class="font-weight-bold">매출미달 적용기간</span>
            </VCol>
            <VCol md="7" cols="6">
                <VChip :color="StatusColorSetter().getSelectIdColor(props.item.under_sales_type || 0)">
                    {{ under_sales_types.find(obj => obj.id === props.item.under_sales_type)?.title }}
                </VChip>
            </VCol>
        </VRow>
    </VCardItem>
</template>
<style scoped>
:deep(.v-row) {
  align-items: center;
}
</style>
