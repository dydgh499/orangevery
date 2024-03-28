<script lang="ts" setup>
import type { PayModule } from '@/views/types'
import { useStore } from '@/views/services/pay-gateways/useStore'
import {ship_out_stats, under_sales_types, comm_settle_types } from '@/views/merchandises/pay-modules/useStore'
import BaseQuestionTooltip from '@/layouts/tooltips/BaseQuestionTooltip.vue'
import CreateHalfVCol from '@/layouts/utils/CreateHalfVCol.vue'
import { getUserLevel, salesLevels } from '@axios'

interface Props {
    item: PayModule,
}
const props = defineProps<Props>()
const { terminals } = useStore()

</script>
<template>
    <VCardItem>
        <!-- 장비 종류 -->
        <VRow v-if="getUserLevel() >= 35">
            <CreateHalfVCol :mdl="5" :mdr="7">
                <template #name>장비 타입</template>
                <template #input>
                    <VSelect :menu-props="{ maxHeight: 400 }" v-model="props.item.terminal_id" :items="terminals"
                        prepend-inner-icon="ic-outline-send-to-mobile" label="장비 선택" item-title="name" item-value="id"
                        single-line />
                </template>
            </CreateHalfVCol>
        </VRow>
        <VRow v-else>
            <CreateHalfVCol :mdl="5" :mdr="7">
                <template #name><span class="font-weight-bold">장비 타입</span></template>
                <template #input>
                    {{ terminals.find(obj => obj.id === props.item.terminal_id)?.name }}
                </template>
            </CreateHalfVCol>
        </VRow>
        <!-- 👉 시리얼 번호 -->
        <VRow v-if="getUserLevel() >= 35">
            <CreateHalfVCol :mdl="5" :mdr="7">
                <template #name>시리얼번호</template>
                <template #input>
                    <VTextField type="text" v-model="props.item.serial_num"
                        prepend-inner-icon="ic-twotone-stay-primary-portrait" placeholder="시리얼번호 입력"
                        persistent-placeholder />
                </template>
            </CreateHalfVCol>
        </VRow>
        <VRow v-else>
            <CreateHalfVCol :mdl="5" :mdr="7">
                <template #name><span class="font-weight-bold">시리얼번호</span></template>
                <template #input>
                    {{ props.item.serial_num }}
                </template>
            </CreateHalfVCol>
        </VRow>
        <!-- 통신비 -->
        <VRow v-if="getUserLevel() >= 35">
            <CreateHalfVCol :mdl="5" :mdr="7">
                <template #name>통신비</template>
                <template #input>
                    <VTextField type="number" v-model="props.item.comm_settle_fee"
                        prepend-inner-icon="tabler-currency-won" placeholder="통신비 입력" persistent-placeholder />
                </template>
            </CreateHalfVCol>
        </VRow>
        <VRow v-else>
            <CreateHalfVCol :mdl="5" :mdr="7">
                <template #name><span class="font-weight-bold">통신비</span></template>
                <template #input>
                    {{ props.item.comm_settle_fee ? props.item.comm_settle_fee.toLocaleString() : '' }}
                </template>
            </CreateHalfVCol>
        </VRow>
        <VRow v-if="getUserLevel() >= 35">
            <CreateHalfVCol :mdl="5" :mdr="7">
                <template #name>
                    <BaseQuestionTooltip :location="'top'" :text="'통신비 정산타입'"
                        :content="'통신비, 통신비 정산타입, 개통일, 정산일, 정산주체가 설정되어있어야 적용됩니다.<br>ex)<br>통신비: 30,000<br>통신비 정산타입: 개통월 M+2부터 적용<br>개통일: 2023-09-25<br>정산일: 1일<br>정산주체: 가맹점<br><br>통신비 차감적용일: 2023-11-01, 2023-12-01, 2024-01-01 ...'">
                    </BaseQuestionTooltip>
                </template>
                <template #input>
                    <VSelect :menu-props="{ maxHeight: 400 }" v-model="props.item.comm_settle_type"
                        :items="comm_settle_types" prepend-inner-icon="ic-baseline-calendar-today" label="정산타입"
                        item-title="title" item-value="id" persistent-hint single-line />
                </template>
            </CreateHalfVCol>
        </VRow>
        <VRow v-else>
            <CreateHalfVCol :mdl="5" :mdr="7">
                <template #name><span class="font-weight-bold">통신비 정산타입</span></template>
                <template #input>
                    {{ comm_settle_types.find(obj => obj.id === props.item.comm_settle_type)?.title }}
                </template>
            </CreateHalfVCol>
        </VRow>
        <VDivider style="margin: 1em 0;" />
        <!-- 👉 매출미달 차감금 -->
        <VRow v-if="getUserLevel() >= 35">
            <CreateHalfVCol :mdl="5" :mdr="7">
                <template #name>매출미달 차감금</template>
                <template #input>
                    <VTextField type="number" v-model="props.item.under_sales_amt"
                        prepend-inner-icon="tabler-currency-won" placeholder="매출미달 차감금 입력" persistent-placeholder />
                </template>
            </CreateHalfVCol>
        </VRow>
        <VRow v-else>
            <CreateHalfVCol :mdl="5" :mdr="7">
                <template #name><span class="font-weight-bold">매출미달 차감금</span></template>
                <template #input>
                    {{ props.item.under_sales_amt ? props.item.under_sales_amt.toLocaleString() : '' }}
                </template>
            </CreateHalfVCol>
        </VRow>
        <!-- 👉 매출미달 하한금액 -->
        <VRow v-if="getUserLevel() >= 35">
            <CreateHalfVCol :mdl="5" :mdr="7">
                <template #name>매출미달 하한금</template>
                <template #input>
                    <VTextField type="number" v-model="props.item.under_sales_limit"
                        prepend-inner-icon="tabler-currency-won" placeholder="매출미달 하한금 입력" persistent-placeholder
                        suffix="만원" />
                </template>
            </CreateHalfVCol>
        </VRow>
        <VRow v-else>
            <CreateHalfVCol :mdl="5" :mdr="7">
                <template #name><span class="font-weight-bold">매출미달 하한금</span></template>
                <template #input>
                    {{ props.item.under_sales_limit ? (props.item.under_sales_limit * 10000).toLocaleString() : '' }}
                </template>
            </CreateHalfVCol>
        </VRow>
        <!-- 👉 매출미달 적용기간 -->
        <VRow v-if="getUserLevel() >= 35">
            <CreateHalfVCol :mdl="5" :mdr="7">
                <template #name>매출미달 적용기간</template>
                <template #input>
                    <VSelect :menu-props="{ maxHeight: 400 }" v-model="props.item.under_sales_type"
                        :items="under_sales_types" prepend-inner-icon="bi:calendar-range" label="적용기간 선택"
                        item-title="title" item-value="id" persistent-hint single-line />
                </template>
            </CreateHalfVCol>
        </VRow>
        <VRow v-else>
            <CreateHalfVCol :mdl="5" :mdr="7">
                <template #name><span class="font-weight-bold">매출미달 적용기간</span></template>
                <template #input>
                    {{ under_sales_types.find(obj => obj.id === props.item.under_sales_type)?.title }}
                </template>
            </CreateHalfVCol>
        </VRow>
        <VDivider style="margin: 1em 0;" />
        <!-- 👉 정산일 -->
        <VRow v-if="getUserLevel() >= 35">
            <CreateHalfVCol :mdl="5" :mdr="7">
                <template #name>정산일</template>
                <template #input>
                    <VTextField v-model="props.item.comm_settle_day" label="정산일 입력" suffix="일" />
                </template>
            </CreateHalfVCol>
        </VRow>
        <VRow v-else>
            <CreateHalfVCol :mdl="5" :mdr="7">
                <template #name><span class="font-weight-bold">정산일</span></template>
                <template #input>
                    {{ props.item.comm_settle_day }}
                </template>
            </CreateHalfVCol>
        </VRow>
        <!-- 👉 정산주체 -->
        <VRow v-if="getUserLevel() >= 35">
            <CreateHalfVCol :mdl="5" :mdr="7">
                <template #name>정산주체</template>
                <template #input>
                    <VSelect :menu-props="{ maxHeight: 400 }" v-model="props.item.comm_calc_level"
                        :items="[{ id: 10, title: '가맹점' }].concat(salesLevels())" prepend-inner-icon="ph:share-network"
                        label="정산자 선택" item-title="title" item-value="id" persistent-hint single-line />
                </template>
            </CreateHalfVCol>
        </VRow>
        <VRow v-else>
            <CreateHalfVCol :mdl="5" :mdr="7">
                <template #name><span class="font-weight-bold">정산주체</span></template>
                <template #input>
                    {{ salesLevels().find(obj => obj.id === props.item.comm_calc_level)?.title }}
                </template>
            </CreateHalfVCol>
        </VRow>
        <!-- 👉 개통일 -->
        <VRow v-if="getUserLevel() >= 35">
            <CreateHalfVCol :mdl="5" :mdr="7">
                <template #name>개통일</template>
                <template #input>
                    <VTextField type="date" v-model="props.item.begin_dt"
                        prepend-inner-icon="ic-baseline-calendar-today" label="개통일 입력" single-line />
                </template>
            </CreateHalfVCol>
        </VRow>
        <VRow v-else>
            <CreateHalfVCol :mdl="5" :mdr="7">
                <template #name><span class="font-weight-bold">개통일</span></template>
                <template #input>
                    {{ props.item.begin_dt }}
                </template>
            </CreateHalfVCol>
        </VRow>
        <!-- 👉 출고일 -->
        <VRow v-if="getUserLevel() >= 35">
            <CreateHalfVCol :mdl="5" :mdr="7">
                <template #name>출고일</template>
                <template #input>
                    <VTextField type="date" v-model="props.item.ship_out_dt"
                        prepend-inner-icon="ic-baseline-calendar-today" label="출고일 입력" single-line />
                </template>
            </CreateHalfVCol>
        </VRow>
        <VRow v-else>
            <CreateHalfVCol :mdl="5" :mdr="7">
                <template #name><span class="font-weight-bold">출고일</span></template>
                <template #input>
                    {{ props.item.ship_out_dt }}
                </template>
            </CreateHalfVCol>
        </VRow>
        <!-- 👉 출고상태 -->
        <VRow v-if="getUserLevel() >= 35">
            <CreateHalfVCol :mdl="5" :mdr="7">
                <template #name>출고상태</template>
                <template #input>
                    <VRadioGroup v-model="props.item.ship_out_stat" inline>
                        <VRadio v-for="(shipOutStat, key) in ship_out_stats" :key="key" :label="shipOutStat.title"
                            :value="shipOutStat.id" />
                    </VRadioGroup>
                </template>
            </CreateHalfVCol>
        </VRow>
        <VRow v-else>
            <CreateHalfVCol :mdl="5" :mdr="7">
                <template #name><span class="font-weight-bold">출고상태</span></template>
                <template #input>
                    {{ ship_out_stats.find(obj => obj.id === props.item.ship_out_stat)?.title }}
                </template>
            </CreateHalfVCol>
        </VRow>
    </VCardItem>
</template>
