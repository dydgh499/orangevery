<script lang="ts" setup>
import { useStore } from '@/views/services/pay-gateways/useStore'
import { useRequestStore } from '@/views/request'
import { requiredValidator, nullValidator } from '@validators'
import type { PayModule, Merchandise } from '@/views/types'
import { module_types, installments, abnormal_trans_limits, shipOutStats } from '@/views/merchandises/pay-modules/useStore'
import { allLevels } from '@/views/salesforces/useStore'
import BooleanRadio from '@/layouts/utils/BooleanRadio.vue'
import CreateHalfVCol from '@/layouts/utils/CreateHalfVCol.vue'
import BaseQuestionTooltip from '@/layouts/tooltips/BaseQuestionTooltip.vue'
import { VForm } from 'vuetify/components'
import corp from '@corp'
import { axios, getUserLevel } from '@axios'

interface Props {
    item: PayModule,
    able_mcht_chanage: boolean,
    merchandises: Merchandise[]
}
const vForm = ref<VForm>()
const props = defineProps<Props>()
const alert = <any>(inject('alert'))
const snackbar = <any>(inject('snackbar'))
const errorHandler = <any>(inject('$errorHandler'))

const all_levels = allLevels()
const { update, remove } = useRequestStore()
const { pgs, pss, settle_types, terminals, psFilter, setFee } = useStore()

const mcht = ref({ id: null, mcht_name: '선택안함' })
const md = ref<number>(3)

const tidCreate = async() => {
    if(await alert.value.show('정말 TID를 새로 가져오시겠습니까?')) {
        try {
            const pg_type = pgs.find(obj => obj.id === props.item.pg_id)?.pg_type
            const r = await axios.post('/api/v1/manager/merchandises/pay-modules/tid-create', { pg_type : pg_type })
            props.item.tid = r.data.tid
            snackbar.value.show('성공하였습니다.<br>저장하시려면 수정버튼을 눌러주세요.', 'success')
        }
        catch (e: any) {
            snackbar.value.show(e.response.data.message, 'error')
            const r = errorHandler(e)
        }
    }
}
const payKeyCreate = async() => {
    if(await alert.value.show('정말 결제 KEY를 신규 발급하시겠습니까?<br><br><b>이전 결제키는 더이상 사용할 수 없으니 주의하시기바랍니다.</b>')) {
        try {
            const r = await axios.post('/api/v1/manager/merchandises/pay-modules/pay-key-create', {id: props.item.id})
            props.item.pay_key = r.data.pay_key
            snackbar.value.show('결제 KEY가 업데이트 되었습니다.', 'success')
        }
        catch (e: any) {
            snackbar.value.show(e.response.data.message, 'error')
            const r = errorHandler(e)
        }
    }
}
const filterPgs = computed(() => {
    const filter = pss.filter(item => { return item.pg_id == props.item.pg_id })
    props.item.ps_id = psFilter(filter, props.item.ps_id)
    return filter
})
onMounted(() => {
    props.item.pg_id = props.item.pg_id == 0 ? null : props.item.pg_id
    props.item.ps_id = props.item.ps_id == 0 ? null : props.item.ps_id
    // 결제모듈 타입 변동 체크
    watchEffect(() => {
        md.value = props.item.module_type == 0 ? 3 : 4
    })
    watchEffect(() => {
        if(props.able_mcht_chanage)
            props.item.mcht_id = mcht.value.id
    })
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
                            <CreateHalfVCol :mdl="5" :mdr="7">
                                <template #name>소유 가맹점</template>
                                <template #input>
                                    <VAutocomplete :menu-props="{ maxHeight: 400 }" v-model="mcht"
                                        :items="props.merchandises" prepend-inner-icon="tabler-building-store" label="가맹점 선택"
                                        item-title="mcht_name" item-value="id" single-line :rules=[nullValidator] return-object />
                                </template>
                            </CreateHalfVCol>
                        </VRow>
                        <!-- 👉 결제 모듈 타입 -->
                        <VRow class="pt-3">
                            <CreateHalfVCol :mdl="5" :mdr="7">
                                <template #name>결제모듈 타입</template>
                                <template #input>
                                    <VSelect :menu-props="{ maxHeight: 400 }" v-model="props.item.module_type"
                                        :items="module_types" prepend-inner-icon="ic-outline-send-to-mobile" label="결제모듈 선택"
                                        item-title="title" item-value="id" single-line :rules=[requiredValidator] />
                                </template>
                            </CreateHalfVCol>
                        </VRow>
                        <!-- 👉 수기결제 타입(구인증, 비인증) -->
                        <VRow class="pt-3" v-show="props.item.module_type == 1 || props.item.module_type == 5">
                            <CreateHalfVCol :mdl="5" :mdr="7">
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
                            <CreateHalfVCol :mdl="5" :mdr="7">
                                <template #name>할부한도</template>
                                <template #input>
                                    <VSelect :menu-props="{ maxHeight: 400 }" v-model="props.item.installment"
                                        :items="installments" prepend-inneer-icon="fluent-credit-card-clock-20-regular"
                                        label="할부한도 선택" item-title="title" item-value="id" single-line />
                                </template>
                            </CreateHalfVCol>
                        </VRow>
                        <!-- 👉 PG사 -->
                        <VRow class="pt-3">
                            <CreateHalfVCol :mdl="5" :mdr="7">
                                <template #name>PG사</template>
                                <template #input>
                                    <VSelect :menu-props="{ maxHeight: 400 }" v-model="props.item.pg_id" :items="pgs"
                                        prepend-inner-icon="ph-buildings" label="PG사 선택" item-title="pg_name" item-value="id"
                                        single-line :rules=[requiredValidator] />
                                </template>
                            </CreateHalfVCol>
                        </VRow>
                        <!-- 👉 PG 구간 -->
                        <VRow class="pt-3">
                            <CreateHalfVCol :mdl="5" :mdr="7">
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
                            <CreateHalfVCol :mdl="5" :mdr="7">
                                <template #name>정산일</template>
                                <template #input>
                                    <VSelect :menu-props="{ maxHeight: 400 }" v-model="props.item.settle_type"
                                        :items="settle_types" prepend-inner-icon="ic-outline-send-to-mobile" label="정산일 선택"
                                        item-title="name" item-value="id" :rules=[requiredValidator] />
                                </template>
                            </CreateHalfVCol>
                        </VRow>
                        <VRow class="pt-3">
                            <CreateHalfVCol :mdl="5" :mdr="7">
                                <template #name>입금 수수료</template>
                                <template #input>
                                    <VTextField v-model="props.item.settle_fee" type="number" suffix="₩"
                                        :rules="[requiredValidator]" />
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
                            <CreateHalfVCol :mdl="5" :mdr="7">
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
                            <CreateHalfVCol :mdl="5" :mdr="7">
                                <template #name>SUB KEY(iv)</template>
                                <template #input>
                                    <VTextField type="text" v-model="props.item.sub_key" prepend-inner-icon="ic-sharp-key"
                                        placeholder="SUB KEY 입력" persistent-placeholder />
                                </template>
                            </CreateHalfVCol>
                        </VRow>
                        <!-- 👉 MID -->
                        <VRow class="pt-3">
                            <CreateHalfVCol :mdl="5" :mdr="7">
                                <template #name>MID</template>
                                <template #input>
                                    <VTextField type="text" v-model="props.item.mid" prepend-inner-icon="tabler-user"
                                        placeholder="MID 입력" persistent-placeholder />
                                </template>
                            </CreateHalfVCol>
                        </VRow>
                        <!-- 👉 TID -->
                        <VRow class="pt-3">
                            <CreateHalfVCol :mdl="5" :mdr="7">
                                <template #name>TID</template>
                                <template #input>
                                    <div style="display: flex; flex-direction: row; justify-content: space-between;">
                                        <VTextField type="text" v-model="props.item.tid" prepend-inner-icon="jam-key-f"
                                            placeholder="TID 입력" persistent-placeholder />
                                        <VBtn type="button" variant="tonal" v-if="getUserLevel() >= 50 && props.item.id == 0"
                                            @click="tidCreate()">
                                            {{ "생성" }}
                                            <VIcon end icon="material-symbols:add-to-home-screen" />
                                        </VBtn>                                        
                                    </div>
                                </template>
                            </CreateHalfVCol>
                        </VRow>
                        <VRow class="pt-3" v-show="props.item.module_type != 0" v-if="props.item.id != 0">
                            <CreateHalfVCol :mdl="5" :mdr="7">
                                <template #name>
                                    <BaseQuestionTooltip :location="'top'" :text="'결제 KEY'"
                                        :content="'해당 키를 통해 온라인 결제를 발생시킬 수 있습니다.<br>키를 복사하려면 입력필드에서 더블클릭하세요.'">
                                    </BaseQuestionTooltip>
                                </template>
                                <template #input>
                                    <div style="display: flex; flex-direction: row; justify-content: space-between;">
                                        <VTextField type="text" v-model="props.item.pay_key" prepend-inner-icon="ic-baseline-vpn-key"
                                             persistent-placeholder :disabled="true"/>

                                        <VBtn type="button" variant="tonal" v-if="getUserLevel() >= 35"
                                            @click="payKeyCreate()">
                                            {{ "발급" }}
                                            <VIcon end icon="material-symbols:add-to-home-screen" />
                                        </VBtn>                                            
                                    </div>
                                </template>
                            </CreateHalfVCol>
                        </VRow>
                    </VCardItem>
                </VCol>
                <VDivider :vertical="$vuetify.display.mdAndUp" v-show="props.item.module_type == 0" />
                <VCol cols="12" :md="md" v-show="props.item.module_type == 0">
                    <VCardItem>
                        <VCardTitle style="margin-bottom: 1em;">장비정보</VCardTitle>
                        <!-- 장비 종류 -->
                        <VRow class="pt-3">
                            <CreateHalfVCol :mdl="5" :mdr="7">
                                <template #name>장비 타입</template>
                                <template #input>
                                    <VSelect :menu-props="{ maxHeight: 400 }" v-model="props.item.terminal_id"
                                        :items="terminals" prepend-inner-icon="ic-outline-send-to-mobile" label="장비 선택"
                                        item-title="name" item-value="id" single-line />
                                </template>
                            </CreateHalfVCol>
                        </VRow>
                        <!-- 👉 시리얼 번호 -->
                        <VRow class="pt-3">
                            <CreateHalfVCol :mdl="5" :mdr="7">
                                <template #name>시리얼번호</template>
                                <template #input>
                                    <VTextField type="text" v-model="props.item.serial_num"
                                        prepend-inner-icon="ic-twotone-stay-primary-portrait" placeholder="시리얼번호 입력"
                                        persistent-placeholder />
                                </template>
                            </CreateHalfVCol>
                        </VRow>
                        <!-- 통신비 -->
                        <VRow class="pt-3">
                            <CreateHalfVCol :mdl="5" :mdr="7">
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
                            <CreateHalfVCol :mdl="5" :mdr="7">
                                <template #name>정산일</template>
                                <template #input>
                                    <VTextField v-model="props.item.comm_settle_type" label="정산일 입력" suffix="일" />
                                </template>
                            </CreateHalfVCol>
                        </VRow>
                        <!-- 👉 정산주체 -->
                        <VRow class="pt-3">
                            <CreateHalfVCol :mdl="5" :mdr="7">
                                <template #name>정산주체</template>
                                <template #input>
                                    <VSelect :menu-props="{ maxHeight: 400 }" v-model="props.item.comm_calc_level"
                                        :items="all_levels" prepend-inner-icon="ph:share-network" label="정산자 선택"
                                        item-title="title" item-value="id" persistent-hint single-line />
                                </template>
                            </CreateHalfVCol>
                        </VRow>
                        <!-- 👉 매출미달 차감금 -->
                        <VRow class="pt-3">
                            <CreateHalfVCol :mdl="5" :mdr="7">
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
                            <CreateHalfVCol :mdl="5" :mdr="7">
                                <template #name>개통일</template>
                                <template #input>
                                    <AppDateTimePicker v-model="props.item.begin_dt"
                                        prepend-inner-icon="ic-baseline-calendar-today" label="개통일 입력" single-line />
                                </template>
                            </CreateHalfVCol>
                        </VRow>
                        <!-- 👉 출고일 -->
                        <VRow class="pt-3">
                            <CreateHalfVCol :mdl="5" :mdr="7">
                                <template #name>출고일</template>
                                <template #input>
                                    <AppDateTimePicker v-model="props.item.ship_out_dt"
                                        prepend-inner-icon="ic-baseline-calendar-today" label="출고일 입력" single-line />
                                </template>
                            </CreateHalfVCol>
                        </VRow>
                        <!-- 👉 출고상태 -->
                        <VRow class="pt-3">
                            <CreateHalfVCol :mdl="5" :mdr="7">
                                <template #name>출고상태</template>
                                <template #input>
                                    <VRadioGroup
                                        v-model="props.item.ship_out_stat"
                                        inline
                                    >
                                        <VRadio
                                            v-for="(shipOutStat, key) in shipOutStats"
                                            :key="key"
                                            :label="shipOutStat.title"
                                            :value="shipOutStat.id"
                                        />
                                    </VRadioGroup>
                                </template>
                            </CreateHalfVCol>
                        </VRow>
                    </VCardItem>
                </VCol>
                <VDivider :vertical="$vuetify.display.mdAndUp" />
                <VCol cols="12" :md="md">
                    <VCardItem>
                        <VCardTitle style="margin-bottom: 1em;">옵션</VCardTitle>
                        <VRow class="pt-3">
                            <CreateHalfVCol :mdl="5" :mdr="7">
                                <template #name>
                                    <BaseQuestionTooltip :location="'top'" :text="'이상거래 한도설정'"
                                        :content="'설정 금액 이상으로 결제가 발생할 시, 이상거래 관리 목록에 추가됩니다.'">
                                    </BaseQuestionTooltip>
                                </template>
                                <template #input>
                                    <VSelect v-model="props.item.abnormal_trans_limit" :items="abnormal_trans_limits"
                                        prepend-inner-icon="jam-triangle-danger" label="이상거래 한도설정" item-title="title"
                                        item-value="id" />
                                </template>
                            </CreateHalfVCol>
                        </VRow>
                        <VRow class="pt-3" v-if="corp.pv_options.paid.use_dup_pay_validation">
                            <CreateHalfVCol :mdl="5" :mdr="7">
                                <template #name>
                                    <BaseQuestionTooltip :location="'top'" :text="'중복결제 허용회수'"
                                        :content="'입력된 카드번호를 통해 중복해서 결제가되었는지 검증합니다.<br>0 입력 시 허용회수를 검증하지 않으며, <b>온라인 결제</b>만 적용 가능합니다.'">
                                    </BaseQuestionTooltip>
                                </template>
                                <template #input>
                                    <VTextField v-model="props.item.pay_dupe_limit" label="중복결제 허용회수" type="number"
                                        suffix="회 허용" :rules="[nullValidator]" />
                                </template>
                            </CreateHalfVCol>
                        </VRow>
                        <VRow class="pt-3" v-if="corp.pv_options.paid.use_pay_limit && props.item.module_type != 0">
                            <CreateHalfVCol :mdl="5" :mdr="7">
                                <template #name>
                                    <BaseQuestionTooltip :location="'top'" :text="'일 결제 한도'"
                                        :content="'결제 한도 금액: 1,000,000원 = 100 입력(이하동일)<br><b>온라인 결제</b>만 적용 가능합니다.'">
                                    </BaseQuestionTooltip>
                                </template>
                                <template #input>
                                    <VTextField prepend-inner-icon="tabler-currency-won" v-model="props.item.pay_day_limit"
                                        type="number" suffix="만원" :rules="[nullValidator]" />
                                </template>
                            </CreateHalfVCol>
                        </VRow>
                        <VRow class="pt-3" v-if="corp.pv_options.paid.use_pay_limit && props.item.module_type != 0">
                            <CreateHalfVCol :mdl="5" :mdr="7">
                                <template #name>월 결제 한도</template>
                                <template #input>
                                    <VTextField prepend-inner-icon="tabler-currency-won"
                                        v-model="props.item.pay_month_limit" type="number" suffix="만원"
                                        :rules="[nullValidator]" />
                                </template>
                            </CreateHalfVCol>
                        </VRow>
                        <VRow class="pt-3" v-if="corp.pv_options.paid.use_pay_limit && props.item.module_type != 0">
                            <CreateHalfVCol :mdl="5" :mdr="7">
                                <template #name>연 결제 한도</template>
                                <template #input>
                                    <VTextField prepend-inner-icon="tabler-currency-won" v-model="props.item.pay_year_limit"
                                        type="number" suffix="만원" :rules="[nullValidator]" />
                                </template>
                            </CreateHalfVCol>
                        </VRow>
                        <VRow class="pt-3" v-if="corp.pv_options.paid.use_forb_pay_time && props.item.module_type != 0">
                            <CreateHalfVCol :mdl="5" :mdr="7">
                                <template #name>
                                    <BaseQuestionTooltip :location="'top'" :text="'결제금지 시간'"
                                        :content="'해당 시간대에는 <b>온라인 결제</b>를 발생시킬 수 없습니다.'">
                                    </BaseQuestionTooltip>
                                </template>
                                <template #input>
                                    <div class="d-flex align-items-center flex-column">
                                        <VTextField v-model="props.item.pay_disable_s_tm" type="time" />
                                        <span class="text-center mx-auto">~</span>
                                        <VTextField v-model="props.item.pay_disable_e_tm" type="time" />
                                    </div>
                                </template>
                            </CreateHalfVCol>
                        </VRow>
                        <VRow class="pt-3" v-if="props.item.module_type != 0">
                            <CreateHalfVCol :mdl="5" :mdr="7">
                                <template #name>결제창 노출여부</template>
                                <template #input>
                                    <BooleanRadio :radio="Boolean(props.item.show_pay_view)"
                                        @update:radio="props.item.show_pay_view = $event">
                                        <template #true>노출</template>
                                        <template #false>숨김</template>
                                    </BooleanRadio>
                                </template>
                            </CreateHalfVCol>
                        </VRow>
                        <!-- 👉 비고 -->
                        <VRow>
                            <VCol>
                                <VTextarea v-model="props.item.note" counter label="결제모듈 별칭"
                                    prepend-inner-icon="twemoji-spiral-notepad" />
                            </VCol>
                        </VRow>
                        <VRow>
                            <VCol class="d-flex gap-4">
                                <VBtn type="button" style="margin-left: auto;"
                                    @click="update('/merchandises/pay-modules', props.item.id, props.item, vForm)">
                                    {{ props.item.id == 0 ? "추가" : "수정" }}
                                    <VIcon end icon="tabler-pencil" />
                                </VBtn>
                                <VBtn color="secondary" variant="tonal" @click="vForm?.reset()">
                                    리셋
                                    <VIcon end icon="tabler-arrow-back" />
                                </VBtn>
                                <VBtn type="button" color="error" v-if="props.item.id"
                                    @click="remove('/merchandises/pay-modules', props.item.id)">
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
