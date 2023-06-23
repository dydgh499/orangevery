<script lang="ts" setup>
import { useRequestStore } from '@/views/request';
import { requiredValidator, nullValidator } from '@validators';
import type { PayModule, Merchandise } from '@/views/types'
import { VForm } from 'vuetify/components';
import { module_types, installments } from '@/views/merchandises/pay-modules/useStore';
import { allLevels } from '@/views/salesforces/useStore';
import { useStore } from '@/views/services/pay-gateways/useStore'
import BooleanRadio from '@/layouts/utils/BooleanRadio.vue';
import CreateHalfVCol from '@/layouts/utils/CreateHalfVCol.vue';

interface Props {
    item: PayModule,
    able_mcht_chanage: boolean,
    mchts: Merchandise[],
}
const vForm = ref<VForm>()
const props = defineProps<Props>()

const all_levels = allLevels()
const { update, remove } = useRequestStore()
const { pgs, pss, settle_types, terminals, setFee, setAmount } = useStore()
const md = ref<number>(3)

onMounted(() => {
    props.item.pg_id = props.item.pg_id == 0 ? null : props.item.pg_id
    props.item.ps_id = props.item.ps_id == 0 ? null : props.item.ps_id
    props.item.settle_type = props.item.settle_type == 0 ? null : props.item.settle_type
    props.item.terminal_id = props.item.terminal_id == 0 ? null : props.item.terminal_id
})

// 결제모듈 타입 변동 체크
watchEffect(() => {
    md.value = props.item.module_type == 0 ? 3 : 4
})

const filterPgs = computed(() => {
    const filter = pss.filter(item => {
        return item.pg_id == props.item.pg_id;
    })
    if (pss.length > 0) {
        if (filter.length > 0) {
            let item = pss.find(item => item.id === props.item.ps_id)
            if (item != undefined && filter[0].pg_id != item.pg_id)
                props.item.ps_id = null
        }
        else
            props.item.ps_id = null
    }
    return filter
})


</script>
<template>
    <AppCardActions action-collapsed :title="props.item.note" :collapsed="props.able_mcht_chanage ? false : true">
        <VDivider />
        <VForm ref="vForm">
            <div class="d-flex justify-space-between flex-wrap flex-md-nowrap flex-column flex-md-row">
                <VCol cols="12" :md="md">
                    <VCardItem>
                        <VCardTitle style="margin-bottom: 1em;">결제타입</VCardTitle>
                        <!-- 👉 결제 모듈 타입 -->
                        <VRow class="pt-3" v-if="props.able_mcht_chanage">
                            <CreateHalfVCol :mdl="6" :mdr="6">
                                <template #name>소유 가맹점</template>
                                <template #input>
                                    <VAutocomplete :menu-props="{ maxHeight: 400 }" v-model="props.item.mcht_id"
                                        :items="props.mchts" prepend-inner-icon="tabler-building-store" label="가맹점 선택"
                                        item-title="mcht_name" item-value="id" single-line :rules=[nullValidator] />
                                </template>
                            </CreateHalfVCol>
                        </VRow>
                        <!-- 👉 결제 모듈 타입 -->
                        <VRow class="pt-3">
                            <CreateHalfVCol :mdl="6" :mdr="6">
                                <template #name>결제모듈 타입</template>
                                <template #input>
                                    <VSelect :menu-props="{ maxHeight: 400 }" v-model="props.item.module_type"
                                        :items="module_types" prepend-inner-icon="ic-outline-send-to-mobile" label="결제모듈 선택"
                                        item-title="title" item-value="id" single-line :rules=[requiredValidator] />
                                </template>
                            </CreateHalfVCol>
                        </VRow>
                        <!-- 👉 수기결제 타입(구인증, 비인증) -->
                        <VRow class="pt-3" v-show="props.item.module_type == 1">
                            <CreateHalfVCol :mdl="6" :mdr="6">
                                <template #name>수기결제 타입</template>
                                <template #input>
                                    <BooleanRadio :radio="Boolean(props.item.is_old_auth)"
                                        @update:radio="props.item.is_old_auth = $event">
                                        <template #true>구인증</template>
                                        <template #false>비인증</template>
                                    </BooleanRadio>
                                </template>
                            </CreateHalfVCol>
                        </VRow>
                        <!-- 👉 할부한도 (수기,인증,간편,실시간,비인증) -->
                        <VRow class="pt-3">
                            <CreateHalfVCol :mdl="6" :mdr="6">
                                <template #name>할부한도</template>
                                <template #input>
                                    <VSelect :menu-props="{ maxHeight: 400 }" v-model="props.item.installment"
                                        :items="installments" prepend-inneer-icon="fluent-credit-card-clock-20-regular"
                                        label="결제모듈 선택" item-title="title" item-value="id" single-line />
                                </template>
                            </CreateHalfVCol>
                        </VRow>
                        <!-- 👉 PG사 -->
                        <VRow class="pt-3">
                            <CreateHalfVCol :mdl="6" :mdr="6">
                                <template #name>PG사</template>
                                <template #input>
                                    <VSelect :menu-props="{ maxHeight: 400 }" v-model="props.item.pg_id" :items="pgs"
                                        prepend-inner-icon="ph-buildings" label="PG사 선택" item-title="pg_nm" item-value="id"
                                        single-line :rules=[requiredValidator] />
                                </template>
                            </CreateHalfVCol>
                        </VRow>
                        <!-- 👉 PG 구간 -->
                        <VRow class="pt-3">
                            <CreateHalfVCol :mdl="6" :mdr="6">
                                <template #name>구간</template>
                                <template #input>
                                    <VSelect :menu-props="{ maxHeight: 400 }" v-model="props.item.ps_id" :items="filterPgs"
                                        prepend-inner-icon="mdi-vector-intersection" label="구간 선택" item-title="name"
                                        item-value="id" :hint="`${setFee(pss, props.item.ps_id)}`" persistent-hint
                                        single-line :rules=[requiredValidator] />
                                </template>
                            </CreateHalfVCol>
                        </VRow>
                        <!-- 👉 정산일 -->
                        <VRow class="pt-3">
                            <CreateHalfVCol :mdl="6" :mdr="6">
                                <template #name>정산일</template>
                                <template #input>
                                    <VSelect :menu-props="{ maxHeight: 400 }" v-model="props.item.settle_type"
                                        :items="settle_types" prepend-inner-icon="ic-outline-send-to-mobile" label="정산일 선택"
                                        item-title="name" item-value="id"
                                        :rules=[requiredValidator] />
                                </template>
                            </CreateHalfVCol>
                        </VRow>
                        <VRow class="pt-3">
                            <CreateHalfVCol :mdl="6" :mdr="6">
                                <template #name>입금 수수료</template>
                                <template #input>
                                    <VTextField v-model="props.item.settle_prem" type="number"
                                        suffix="₩" :rules="[requiredValidator]" />
                                </template>
                            </CreateHalfVCol>
                        </VRow>
                    </VCardItem>
                </VCol>
                <VDivider :vertical="$vuetify.display.mdAndUp" />
                <VCol cols="12" :md="md">
                    <VCardItem>
                        <VCardTitle style="margin-bottom: 1em;">결제정보</VCardTitle>
                        <!-- 👉 API KEY-->
                        <VRow class="pt-3" v-show="props.item.module_type != 0">
                            <CreateHalfVCol :mdl="6" :mdr="6">
                                <template #name>API KEY(license)</template>
                                <template #input>
                                    <VTextField type="text" v-model="props.item.api_key"
                                        prepend-inner-icon="ic-baseline-vpn-key" placeholder="API KEY 입력"
                                        persistent-placeholder />
                                </template>
                            </CreateHalfVCol>
                        </VRow>

                        <!-- 👉 SUB KEY-->
                        <VRow class="pt-3" v-show="props.item.module_type != 0">
                            <CreateHalfVCol :mdl="6" :mdr="6">
                                <template #name>SUB KEY(license)</template>
                                <template #input>
                                    <VTextField type="text" v-model="props.item.sub_key" prepend-inner-icon="ic-sharp-key"
                                        placeholder="SUB KEY 입력" persistent-placeholder />
                                </template>
                            </CreateHalfVCol>
                        </VRow>
                        <!-- 👉 MID -->
                        <VRow class="pt-3">
                            <CreateHalfVCol :mdl="6" :mdr="6">
                                <template #name>MID</template>
                                <template #input>
                                    <VTextField type="text" v-model="props.item.mid" prepend-inner-icon="tabler-user"
                                        placeholder="MID 입력" persistent-placeholder />
                                </template>
                            </CreateHalfVCol>
                        </VRow>
                        <!-- 👉 TID -->
                        <VRow class="pt-3">
                            <CreateHalfVCol :mdl="6" :mdr="6">
                                <template #name>TID</template>
                                <template #input>
                                    <VTextField type="text" v-model="props.item.tid" prepend-inner-icon="jam-key-f"
                                        placeholder="TID 입력" persistent-placeholder />
                                </template>
                            </CreateHalfVCol>
                        </VRow>

                        <!-- 👉 시리얼 번호 -->
                        <VRow class="pt-3" v-show="props.item.module_type == 0">
                            <CreateHalfVCol :mdl="6" :mdr="6">
                                <template #name>시리얼번호</template>
                                <template #input>
                                    <VTextField type="text" v-model="props.item.serial_num"
                                        prepend-inner-icon="ic-twotone-stay-primary-portrait" placeholder="시리얼번호 입력"
                                        persistent-placeholder />
                                </template>
                            </CreateHalfVCol>
                        </VRow>
                    </VCardItem>
                </VCol>
                <VDivider :vertical="$vuetify.display.mdAndUp" v-show="props.item.module_type == 0" />
                <VCol cols="12" :md="md" v-show="props.item.module_type == 0">
                    <VCardItem>
                        <VCardTitle style="margin-bottom: 1em;">단말기정보</VCardTitle>
                        <!-- 단말기 종류 -->
                        <VRow class="pt-3">
                            <CreateHalfVCol :mdl="6" :mdr="6">
                                <template #name>단말기 타입</template>
                                <template #input>
                                    <VSelect :menu-props="{ maxHeight: 400 }" v-model="props.item.terminal_id"
                                        :items="terminals" prepend-inner-icon="ic-outline-send-to-mobile" label="단말기 선택"
                                        item-title="name" item-value="id" single-line persistent-hint
                                        :hint="`${setAmount(terminals, props.item.terminal_id)}`" />
                                </template>
                            </CreateHalfVCol>
                        </VRow>
                        <!-- 통신비 -->
                        <VRow class="pt-3">
                            <CreateHalfVCol :mdl="6" :mdr="6">
                                <template #name>통신비</template>
                                <template #input>
                                    <VTextField type="number" v-model="props.item.comm_settle_fee"
                                        prepend-inner-icon="tabler-currency-won" placeholder="통신비 입력"
                                        persistent-placeholder />
                                </template>
                            </CreateHalfVCol>
                        </VRow>
                        <!-- 👉 정산일 -->
                        <VRow class="pt-3">
                            <CreateHalfVCol :mdl="6" :mdr="6">
                                <template #name>정산일</template>
                                <template #input>
                                    <VTextField v-model="props.item.comm_settle_type"
                                    :rules="[v => (v >= 0 && v <= 31) || '0~31 사이의 값을 입력해주세요']"
                                    label="정산일 입력" suffix="일" />
                                </template>
                            </CreateHalfVCol>
                        </VRow>
                        <!-- 👉 정산주체 -->
                        <VRow class="pt-3">
                            <CreateHalfVCol :mdl="6" :mdr="6">
                                <template #name>정산주체</template>
                                <template #input>
                                    <VSelect :menu-props="{ maxHeight: 400 }" v-model="props.item.comm_calc_level"
                                        :items="all_levels" prepend-inner-icon="tabler-man" label="정산자 선택" item-title="title"
                                        item-value="id" persistent-hint single-line />
                                </template>
                            </CreateHalfVCol>
                        </VRow>
                        <!-- 👉 매출미달 차감금 -->
                        <VRow class="pt-3">
                            <CreateHalfVCol :mdl="6" :mdr="6">
                                <template #name>매출미달 차감금</template>
                                <template #input>
                                    <VTextField type="number" v-model="props.item.under_sales_amt"
                                        prepend-inner-icon="tabler-currency-won" placeholder="매출미달 차감금 입력"
                                        persistent-placeholder />
                                </template>
                            </CreateHalfVCol>
                        </VRow>
                        <!-- 👉 개통일 -->
                        <VRow class="pt-3">
                            <CreateHalfVCol :mdl="6" :mdr="6">
                                <template #name>개통일</template>
                                <template #input>
                                    <AppDateTimePicker v-model="props.item.begin_dt"
                                        prepend-inner-icon="ic-baseline-calendar-today" label="개통일 입력" single-line />
                                </template>
                            </CreateHalfVCol>
                        </VRow>
                        <!-- 👉 출고일 -->
                        <VRow class="pt-3">
                            <CreateHalfVCol :mdl="6" :mdr="6">
                                <template #name>출고일</template>
                                <template #input>
                                    <AppDateTimePicker v-model="props.item.ship_out_dt"
                                        prepend-inner-icon="ic-baseline-calendar-today" label="출고일 입력" single-line />
                                </template>
                            </CreateHalfVCol>
                        </VRow>
                        <!-- 👉 출고상태 -->
                        <VRow class="pt-3">
                            <CreateHalfVCol :mdl="6" :mdr="6">
                                <template #name>출고상태</template>
                                <template #input>
                                    <BooleanRadio :radio="Boolean(props.item.ship_out_stat)"
                                        @update:radio="props.item.ship_out_stat = $event">
                                        <template #true>출고</template>
                                        <template #false>입고</template>
                                    </BooleanRadio>
                                </template>
                            </CreateHalfVCol>
                        </VRow>
                    </VCardItem>
                </VCol>
                <VDivider :vertical="$vuetify.display.mdAndUp" />
                <VCol cols="12" :md="md">
                    <VCardItem>
                        <VCardTitle style="margin-bottom: 1em;">옵션</VCardTitle>
                        <!-- 👉 매출전표 공급자 사용 여부 -->
                        <VRow>
                            <CreateHalfVCol :mdl="6" :mdr="6">
                                <template #name>매출전표 공급자 정보</template>
                                <template #input>
                                    <BooleanRadio :radio="Boolean(props.item.use_saleslip_prov)"
                                        @update:radio="props.item.use_saleslip_prov = $event">
                                        <template #true>본사</template>
                                        <template #false>가맹점</template>
                                    </BooleanRadio>
                                </template>
                            </CreateHalfVCol>
                        </VRow>
                        <!-- 👉 매출전표 판매자 사용 여부 -->
                        <VRow>
                            <CreateHalfVCol :mdl="6" :mdr="6">
                                <template #name>매출전표 판매자 정보</template>
                                <template #input>
                                    <BooleanRadio :radio="Boolean(props.item.use_saleslip_sell)"
                                        @update:radio="props.item.use_saleslip_prov = $event">
                                        <template #true>본사</template>
                                        <template #false>가맹점</template>
                                    </BooleanRadio>
                                </template>
                            </CreateHalfVCol>
                        </VRow>
                        <!-- 👉 비고 -->
                        <VRow>
                            <VCol>
                                <VTextarea v-model="props.item.note" counter label="비고(별칭)"
                                    prepend-inner-icon="twemoji-spiral-notepad" />
                            </VCol>
                        </VRow>
                        <VRow>
                            <VCol class="d-flex gap-4">
                                <VBtn type="button" style="margin-left: auto;" @click="update('/merchandises/pay-modules', props.item.id, props.item, vForm)">
                                    {{ props.item.id == 0 ? "추가" : "수정" }}
                                    <VIcon end icon="tabler-pencil" />
                                </VBtn>
                                <VBtn color="secondary" variant="tonal" @click="vForm?.reset()">
                                    리셋
                                    <VIcon end icon="tabler-arrow-back" />
                                </VBtn>
                                <VBtn type="button" color="error" v-if="props.item.id" @click="remove('/merchandises/pay-modules', props.item.id)">
                                    삭제
                                    <VIcon end icon="tabler-trash" />
                                </VBtn>
                            </VCol>
                        </VRow>
                    </VCardItem>
                </VCol>
            </div>
        </VForm>
    </AppCardActions>
</template>
