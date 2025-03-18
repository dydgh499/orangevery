<script lang="ts" setup>
import corp from '@/plugins/corp';
import { fin_trx_delays, withdraw_limit_types } from '@/views/merchandises/pay-modules/useStore';
import { StatusColorSetter } from '@/views/searcher';
import { useStore } from '@/views/services/pay-gateways/useStore';
import type { PayModule } from '@/views/types';
import { axios, getUserLevel, isAbleModiyV2 } from '@axios';
import { requiredValidatorV2 } from '@validators';

interface Props {
    item: PayModule,
}

const props = defineProps<Props>()
const alert = <any>(inject('alert'))
const snackbar = <any>(inject('snackbar'))
const errorHandler = <any>(inject('$errorHandler'))
const midCreateDlg = <any>(inject('midCreateDlg'))
const is_readonly_fin_trx_delay = ref(false)
const occuerred_sale_load = ref(false)

const { pgs, finance_vans, settle_types } = useStore()

const tidCreate = async() => {
    if(await alert.value.show('정말 TID를 신규 발급하시겠습니까?')) {
        try {
            const pg_type = pgs.find(obj => obj.id === props.item.pg_id)?.pg_type
            if(pg_type) {
                const r = await axios.post('/api/v1/manager/merchandises/pay-modules/tid-create', { pg_type : pg_type })
                props.item.tid = r.data.tid
                snackbar.value.show('성공하였습니다.<br>저장하시려면 추가버튼을 눌러주세요.', 'success')
            }
            else
                snackbar.value.show('PG사를 먼저 선택해주세요.', 'warning')
        }
        catch (e: any) {
            snackbar.value.show(e.response.data.message, 'error')
            const r = errorHandler(e)
        }
    }
}

const midCreate = async() => {
    const mid_code = await midCreateDlg.value.show()
    if(mid_code) {
        const r = await axios.post('/api/v1/manager/merchandises/pay-modules/mid-create', {mid_code: mid_code})    
        if(r.status == 200)
            props.item.mid = r.data.mid
        else
            snackbar.value.error(r.data.message, 'error')
    }
}

const payKeyCreate = async() => {
    if(await alert.value.show('정말 결제 KEY를 신규 발급하시겠습니까?<br><br><b>이전 결제 KEY는 더이상 사용할 수 없으니 주의하시기바랍니다.</b>')) {
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

const signKeyCreate = async() => {
    if(await alert.value.show('정말 서명 KEY를 신규 발급하시겠습니까?<br><br><b>이전 서명 KEY는 더이상 사용할 수 없으니 주의하시기바랍니다.</b>')) {
        try {
            const r = await axios.post('/api/v1/manager/merchandises/pay-modules/sign-key-create', {id: props.item.id})
            props.item.sign_key = r.data.sign_key
            snackbar.value.show('서명 KEY가 업데이트 되었습니다.', 'success')
        }
        catch (e: any) {
            snackbar.value.show(e.response.data.message, 'error')
            const r = errorHandler(e)
        }
    }
}

const useCollectWithdrawTrxFinDelayValidate = () => {
    if (isAbleModiyV2(props.item, 'merchandises/pay-modules') && props.item.use_realtime_deposit && props.item.id) {
        axios.get('/api/v1/bf/occuerred-sale', {params: {
            mcht_id: props.item.mcht_id,
            pmod_id: props.item.id
        }}).then( r => {
            is_readonly_fin_trx_delay.value = r.data.exist
            occuerred_sale_load.value = true
        }).catch(e => {})
    }
}
watchEffect(() => {
    if(occuerred_sale_load.value === false)
        useCollectWithdrawTrxFinDelayValidate()
})

</script>
<template>
    <VCardItem>
        <VCardSubtitle style="display: flex; justify-content: space-between;">
            <VChip variant="outlined">계약 및 결제 정보</VChip>
            <template v-if="isAbleModiyV2(props.item, 'merchandises/pay-modules')">
                <div style="display: inline-block;">
                    <VBtn type="button" variant="tonal" v-if="corp.pv_options.paid.use_mid_create && props.item.id == 0 && getUserLevel() >= 35" 
                        @click="midCreate()" style="margin-left: 0.5em;" size="small" color="info">
                        {{ "MID 발급" }}
                    </VBtn>
                    <VBtn type="button" variant="tonal" v-if="corp.pv_options.paid.use_tid_create && props.item.id == 0 && getUserLevel() >= 35" 
                        @click="tidCreate()" style="margin-left: 0.5em;" size="small" color="info">
                        {{ "TID 발급" }}
                    </VBtn>
                </div>
            </template>
        </VCardSubtitle>
        <br>
        <VRow v-if="isAbleModiyV2(props.item, 'merchandises/pay-modules')">
            <VCol md="6" cols="12">
                <VSelect :menu-props="{ maxHeight: 400 }" v-model="props.item.settle_type" :items="settle_types"
                        prepend-inner-icon="ic-outline-send-to-mobile" item-title="name" item-value="id" label="정산일"
                        :rules="[requiredValidatorV2(props.item.settle_type, '정산일')]" />
            </VCol>
            <VCol md="6" cols="12">                    
                <VTextField v-model="props.item.settle_fee" type="number" suffix="원" label="단건 수수료"
                        :rules="[requiredValidatorV2(props.item.settle_fee, '단건 수수료')]" />
            </VCol>
        </VRow>
        <VRow v-else>
            <VCol md="5" cols="6">
                <span class="font-weight-bold">정산일</span>    
            </VCol>
            <VCol md="7" cols="6">
                {{ settle_types.find(obj => obj.id === props.item.settle_type)?.name }}
            </VCol>
            <VCol md="5" cols="6">
                <span class="font-weight-bold">이체 수수료</span>
            </VCol>
            <VCol md="7" cols="6">
                {{ props.item.settle_fee }}원
            </VCol>
        </VRow>
        <VRow v-if="isAbleModiyV2(props.item, 'merchandises/pay-modules')">
            <VCol md="6" cols="12">
                <VTextField type="text" v-model="props.item.mid" prepend-inner-icon="tabler-user"
                    placeholder="MID 입력" persistent-placeholder label="MID"
                    maxlength="50"/>
            </VCol>
            <VCol md="6">
                <VTextField type="text" v-model="props.item.tid" prepend-inner-icon="jam-key-f"
                    placeholder="TID 입력" persistent-placeholder label="TID"
                    maxlength="50"/>
            </VCol>
        </VRow>
        <VRow v-else>
            <VCol md="5" cols="6">
                <span class="font-weight-bold">MID</span>
            </VCol>
            <VCol md="7" cols="6">
                {{ props.item.mid }}
            </VCol>
            <VCol md="5" cols="6">
                <span class="font-weight-bold">TID</span>
            </VCol>
            <VCol md="7" cols="6">
                {{ props.item.tid }}
            </VCol>
        </VRow>
        <VRow v-if="isAbleModiyV2(props.item, 'merchandises/pay-modules')">
            <VCol md="6" cols="12">
                <AppDateTimePicker 
                    v-model="props.item.contract_s_dt" 
                    prepend-inner-icon="ic-baseline-calendar-today"
                    placeholder="시작일 입력"
                    label="계약 시작일"
                    />
            </VCol>
            <VCol md="6">
                <AppDateTimePicker 
                    v-model="props.item.contract_e_dt" 
                    prepend-inner-icon="ic-baseline-calendar-today" 
                    placeholder="종료일 입력" 
                    label="계약 종료일"
                    />
            </VCol>
            <VTooltip activator="parent" location="top" transition="scale-transition">
                결제일이 계약 시작일 ~ 계약 종료일에 포함되지 않을 시 결제가 불가능합니다.<br>
                입력하지 않을 시 검증하지 않으며 <b>온라인 결제</b>만 적용 가능합니다.
            </VTooltip>
        </VRow>
        <VRow v-else>
            <VCol md="5" cols="6"><span class="font-weight-bold">계약 시작일</span></VCol>
            <VCol md="7" cols="6">
                {{ props.item.contract_s_dt }}
            </VCol>
            <VCol md="5" cols="6">
                <span class="font-weight-bold">계약 종료일</span>    
            </VCol>
            <VCol md="7" cols="6">
                {{ props.item.contract_e_dt }}
            </VCol>
        </VRow>

        <template v-if="corp.pv_options.paid.use_online_pay && props.item.id != 0 && props.item.module_type != 0">
            <VRow v-if="isAbleModiyV2(props.item, 'merchandises/pay-modules')">
                <VCol md="4" cols="4">
                    <VBtn type="button" variant="tonal" @click="payKeyCreate()" size="small" color="info">
                        {{ "결제 KEY 발급" }}                            
                        <VTooltip activator="parent" location="top">
                            해당 키를 통해 온라인 결제를 발생시킬 수 있습니다.
                        </VTooltip>
                    </VBtn>
                </VCol>
                <VCol md="8" cols="8">
                    <div style="display: flex; flex-direction: row; justify-content: space-between;">
                        <VTextField type="text" v-model="props.item.pay_key" prepend-inner-icon="ic-baseline-vpn-key"
                            label="결제 KEY" :disabled="true"/>
                        <VTooltip activator="parent" location="top">
                            더블클릭해서 결제 KEY를 복사하세요.
                        </VTooltip>
                    </div>
                </VCol>
            </VRow>
            <VRow v-else>
                <VCol md="5" cols="6">
                    <span class="font-weight-bold">결제 KEY</span>    
                </VCol>
                <VCol md="7" cols="12">
                    <span style="background-color: rgba(var(--v-theme-on-surface));">
                        {{ props.item.pay_key }}
                        <VTooltip activator="parent" location="top" v-if="props.item.pay_key">
                            더블클릭또는 드래그하여 결제 KEY를 복사하세요.
                        </VTooltip>
                    </span>
                </VCol>
            </VRow>
        </template>

        <template v-if="corp.pv_options.paid.use_noti && props.item.id != 0">
            <VRow v-if="isAbleModiyV2(props.item, 'merchandises/pay-modules')">
                <VCol md="4" cols="4">
                    <VBtn type="button" variant="tonal" @click="signKeyCreate()" size="small" color="info">
                        {{ "서명 KEY 발급" }}
                        <VTooltip activator="parent" location="top">
                            노티발송시 데이터 위변조 방지 값으로 사용됩니다.
                        </VTooltip>
                    </VBtn>
                </VCol>
                <VCol md="8" cols="12">
                    <div style="display: flex; flex-direction: row; justify-content: space-between;">
                        <VTextField type="text" v-model="props.item.sign_key" prepend-inner-icon="ic-baseline-vpn-key"
                            :disabled="true" label="서명 KEY"/>
                        <VTooltip activator="parent" location="top" v-if="props.item.sign_key">
                            더블클릭해서 서명 KEY를 복사하세요.
                        </VTooltip>
                    </div>
                </VCol>
            </VRow>
            <VRow v-else>
                <VCol md="5" cols="6">
                    <span class="font-weight-bold">서명 KEY</span>    
                </VCol>
                <VCol md="7" cols="12">
                    <span style="background-color: rgba(var(--v-theme-on-surface));">
                        {{ props.item.sign_key }}
                        <VTooltip activator="parent" location="top">
                            더블클릭또는 드래그하여 서명 KEY를 복사하세요.
                        </VTooltip>
                    </span>
                </VCol>
            </VRow>
        </template>

        <template v-if="corp.pv_options.paid.use_realtime_deposit">
            <template v-if="isAbleModiyV2(props.item, 'merchandises/pay-modules')">
                <VDivider style="margin: 1em 0;" />
                    <VCardSubtitle style="display: flex; align-items: center; justify-content: space-between;">
                        <VChip variant="outlined">출금정보</VChip>
                        <div style="display: inline-block;">
                            <VSwitch 
                                hide-details :false-value=0 :true-value=1 
                                v-model="props.item.use_realtime_deposit"
                                label="출금사용여부" color="warning"
                            />
                        </div>
                    </VCardSubtitle>
                    <br>
                    <VRow>
                        <VCol md="6" cols="12">
                            <VSelect :menu-props="{ maxHeight: 400 }" v-model="props.item.fin_id" :items="finance_vans"
                                prepend-inner-icon="streamline-emojis:ant" label="이체모듈 타입" item-title="nick_name"
                                item-value="id" />
                        </VCol>
                        <VCol md="6">
                            <VSelect :menu-props="{ maxHeight: 400 }" v-model="props.item.fin_trx_delay"
                                :items="fin_trx_delays" prepend-inner-icon="streamline-emojis:bug" label="이체딜레이 선택"
                                item-title="title" item-value="id" :readonly="is_readonly_fin_trx_delay"/>
                            <VTooltip activator="parent" location="top">
                                사고 방지를 위해 결제모듈이 최초거래가 발생한 순간부터 이체 딜레이를 수정할 수 없습니다.
                            </VTooltip>
                        </VCol>
                    </VRow>
            </template>
            <VDivider style="margin: 1em 0;" />
            <VCardSubtitle style="display: flex; align-items: center; justify-content: space-between;">
                <VChip variant="outlined">출금제한</VChip>
                <template v-if="isAbleModiyV2(props.item, 'merchandises/pay-modules') === false">
                    <VChip :color="props.item.use_realtime_deposit ? 'success' : 'error'">
                        {{ props.item.use_realtime_deposit ? "출금사용" : "출금미사용" }}
                    </VChip>
                </template>
            </VCardSubtitle>
            <br>
            <VRow v-if="isAbleModiyV2(props.item, 'merchandises/pay-modules')">
                <VCol md="6">
                    <VSelect :menu-props="{ maxHeight: 400 }" v-model="props.item.withdraw_limit_type"
                        :items="withdraw_limit_types" prepend-inner-icon="streamline-emojis:pig" label="출금제한타입"
                        item-title="title" item-value="id"/>
                </VCol>
            </VRow>
            <VRow v-else>
                <VCol md="5" cols="6">
                    <span class="font-weight-bold">출금제한타입</span>
                </VCol>
                <VCol md="7" cols="6">
                    <VChip :color="StatusColorSetter().getSelectIdColor(props.item.withdraw_limit_type || 0)">
                        {{ withdraw_limit_types.find(obj => obj.id === props.item.withdraw_limit_type)?.title }}
                    </VChip>
                </VCol>
            </VRow>

            <VRow v-if="isAbleModiyV2(props.item, 'merchandises/pay-modules')">
                <VCol md="6">
                    <VTextField prepend-inner-icon="tabler-currency-won"
                            v-model="props.item.withdraw_business_limit" type="number" suffix="만원" label="일 출금한도(영업일)"/>
                </VCol>
                <VCol md="6">
                    <VTextField prepend-inner-icon="tabler-currency-won"
                            v-model="props.item.withdraw_holiday_limit" type="number" suffix="만원" label="일 출금한도(휴무일)"/>
                </VCol>
            </VRow>
            <VRow v-else>
                <VCol md="5" cols="6">
                    <span class="font-weight-bold">일 출금한도(영업일)</span>
                </VCol>
                <VCol md="7" cols="6">
                    {{ props.item.withdraw_business_limit }} 만원
                </VCol>
                <VCol md="5" cols="6">
                    <span class="font-weight-bold">일 출금한도(휴무일)</span>
                </VCol>
                <VCol md="7" cols="6">
                    {{ props.item.withdraw_holiday_limit }} 만원
                </VCol>
            </VRow>
        </template>
    </VCardItem>
</template>
<style scoped>
:deep(.v-row) {
  align-items: center;
}
</style>
