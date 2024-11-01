<script lang="ts" setup>
import BaseQuestionTooltip from '@/layouts/tooltips/BaseQuestionTooltip.vue'
import BooleanRadio from '@/layouts/utils/BooleanRadio.vue'
import { fin_trx_delays } from '@/views/merchandises/pay-modules/useStore'
import { useStore } from '@/views/services/pay-gateways/useStore'
import type { PayModule } from '@/views/types'
import { axios, getUserLevel, isAbleModiy } from '@axios'
import corp from '@corp'

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

const { pgs, finance_vans } = useStore()

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
    if (isAbleModiy(props.item.id) && props.item.use_realtime_deposit && props.item.id) {
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
        <VRow v-if="isAbleModiy(props.item.id)">
            <VCol md="5" cols="4">API KEY</VCol>
            <VCol md="7">
                <VTextField type="text" v-model="props.item.api_key" prepend-inner-icon="ic-baseline-vpn-key"
                        placeholder="API KEY 입력" persistent-placeholder maxlength="100"/>
            </VCol>
        </VRow>
        <VRow v-if="isAbleModiy(props.item.id)">
            <VCol md="5" cols="4">SUB KEY</VCol>
            <VCol md="7">
                <VTextField type="text" v-model="props.item.sub_key" prepend-inner-icon="ic-sharp-key"
                        placeholder="SUB KEY 입력" persistent-placeholder maxlength="100"/>
            </VCol>
        </VRow>
        <VRow v-if="isAbleModiy(props.item.id) && corp.pv_options.paid.use_pmid">
            <VCol md="5" cols="4">PMID</VCol>
            <VCol md="7">
                <VTextField type="text" v-model="props.item.p_mid" prepend-inner-icon="tabler-user"
                        placeholder="PMID 입력" persistent-placeholder maxlength="50"/>
            </VCol>
        </VRow>
        <VRow v-if="isAbleModiy(props.item.id)">
            <VCol md="5" cols="4">
                <div style="display: flex; flex-direction: row;align-items: center;">
                    <span>MID</span>
                    <VBtn type="button" variant="tonal" v-if="props.item.id == 0 && corp.pv_options.paid.use_mid_create && getUserLevel() >= 35" 
                        @click="midCreate()" style="margin-left: 0.5em;" size="small" color="info">
                        {{ "발급하기" }}
                    </VBtn>
                </div>
            </VCol>
            <VCol md="7">
                <VTextField type="text" v-model="props.item.mid" prepend-inner-icon="tabler-user"
                    placeholder="MID 입력" persistent-placeholder
                    maxlength="50"/>
            </VCol>
        </VRow>
        <VRow v-else>
            <VCol md="5" cols="4">
                <span class="font-weight-bold">MID</span>
            </VCol>
            <VCol md="7">
                {{ props.item.mid }}
            </VCol>
        </VRow>
        <VRow v-if="isAbleModiy(props.item.id)">
            <VCol md="5" cols="4">
                <div style="display: flex; flex-direction: row;align-items: center;">
                    <span>TID</span>
                    <VBtn type="button" variant="tonal" v-if="props.item.id == 0 && corp.pv_options.paid.use_tid_create && getUserLevel() >= 35" 
                        @click="tidCreate()" style="margin-left: 0.5em;" size="small" color="info">
                        {{ "발급하기" }}
                    </VBtn>
                </div>
            </VCol>
            <VCol md="7">
                <VTextField type="text" v-model="props.item.tid" prepend-inner-icon="jam-key-f"
                    placeholder="TID 입력" persistent-placeholder
                    maxlength="50"/>
            </VCol>
        </VRow>
        <VRow v-else>
            <VCol md="5" cols="4">
                <span class="font-weight-bold">TID</span>
            </VCol>
            <VCol md="7">
                {{ props.item.tid }}
            </VCol>
        </VRow>

        <VRow v-if="isAbleModiy(props.item.id)">
            <VCol md="5" cols="5">계약 시작일</VCol>
            <VCol md="7">
                <AppDateTimePicker 
                    v-model="props.item.contract_s_dt" 
                    prepend-inner-icon="ic-baseline-calendar-today"
                    placeholder="시작일 입력"
                    />
            </VCol>
        </VRow>
        <VRow v-else>
            <VCol md="5" cols="5"><span class="font-weight-bold">계약 시작일</span></VCol>
            <VCol md="7">
                {{ props.item.contract_s_dt }}
            </VCol>
        </VRow>

        <VRow v-if="isAbleModiy(props.item.id)">
            <VCol md="5" cols="5">
                <BaseQuestionTooltip :location="'top'" :text="'계약 종료일'"
                    :content="'결제일이 계약 시작일 ~ 계약 종료일에 포함되지 않을 시 결제가 불가능합니다.<br>입력하지 않을 시 검증하지 않으며 <b>온라인 결제</b>만 적용 가능합니다.'"/>
            </VCol>
            <VCol md="7">
                <AppDateTimePicker 
                    v-model="props.item.contract_e_dt" 
                    prepend-inner-icon="ic-baseline-calendar-today" 
                    placeholder="종료일 입력" 
                    />
            </VCol>
        </VRow>
        <VRow v-else>
            <VCol md="5" cols="5">
                <span class="font-weight-bold">계약 종료일</span>    
            </VCol>
            <VCol md="7">
                {{ props.item.contract_e_dt }}
            </VCol>
        </VRow>
        <template v-if="props.item.id != 0 && props.item.module_type != 0 && corp.pv_options.paid.use_online_pay">
            <VRow v-if="isAbleModiy(props.item.id) && getUserLevel() >= 35">
                <VCol md="6" cols="4">
                    <div style="display: flex; flex-direction: row;align-items: center;">
                        <span>결제 KEY</span>
                        <VBtn type="button" variant="tonal" @click="payKeyCreate()" style="margin-left: 0.5em;" size="small" color="info">
                            {{ "발급하기" }}                            
                            <VTooltip activator="parent" location="top">
                                해당 키를 통해 온라인 결제를 발생시킬 수 있습니다.
                            </VTooltip>
                        </VBtn>
                    </div>
                </VCol>
                <VCol md="6">
                    <div style="display: flex; flex-direction: row; justify-content: space-between;">
                        <VTextField type="text" v-model="props.item.pay_key" prepend-inner-icon="ic-baseline-vpn-key"
                            persistent-placeholder :disabled="true"/>
                        <VTooltip activator="parent" location="top">
                            더블클릭해서 결제 KEY를 복사하세요.
                        </VTooltip>
                    </div>
                </VCol>
            </VRow>
            <VRow v-else>
                <VCol md="5" cols="4">
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

        <template v-if="props.item.id != 0 && corp.pv_options.paid.use_noti">
            <VRow v-if="isAbleModiy(props.item.id) && getUserLevel() >= 35">
                <VCol md="6" cols="4">
                    <div style="display: flex; flex-direction: row;align-items: center;">
                        <span>서명 KEY</span>
                        <VBtn type="button" variant="tonal" @click="signKeyCreate()" style="margin-left: 0.5em;" size="small" color="info">
                            {{ "발급하기" }}
                            <VTooltip activator="parent" location="top">
                                노티발송시 데이터 위변조 방지 값으로 사용됩니다.
                            </VTooltip>
                        </VBtn>
                    </div>
                </VCol>
                <VCol md="6">
                    <div style="display: flex; flex-direction: row; justify-content: space-between;">
                        <VTextField type="text" v-model="props.item.sign_key" prepend-inner-icon="ic-baseline-vpn-key"
                            persistent-placeholder :disabled="true"/>
                        <VTooltip activator="parent" location="top" v-if="props.item.sign_key">
                            더블클릭해서 서명 KEY를 복사하세요.
                        </VTooltip>
                    </div>
                </VCol>
            </VRow>
            <VRow v-else>
                <VCol md="5" cols="4">
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
        
        <template v-if="isAbleModiy(props.item.id) && corp.pv_options.paid.use_realtime_deposit">
            <VDivider style="margin: 1em 0;" />
            <VRow>
                <VCol md="5" cols="5">실시간 사용여부</VCol>
                <VCol md="7">
                    <BooleanRadio :radio="props.item.use_realtime_deposit"
                            @update:radio="props.item.use_realtime_deposit = $event">
                            <template #true>사용</template>
                            <template #false>미사용</template>
                    </BooleanRadio>
                </VCol>
            </VRow>
            <VRow>
                <VCol md="5" cols="5">이체 모듈 타입</VCol>
                <VCol md="7">
                    <VSelect :menu-props="{ maxHeight: 400 }" v-model="props.item.fin_id" :items="finance_vans"
                            prepend-inner-icon="streamline-emojis:ant" label="모듈 타입 선택" item-title="nick_name"
                            item-value="id" single-line />
                </VCol>
            </VRow>
            <VRow>
                <VCol md="5" cols="5">이체 딜레이</VCol>
                <VCol md="7">
                    <VSelect :menu-props="{ maxHeight: 400 }" v-model="props.item.fin_trx_delay"
                        :items="fin_trx_delays" prepend-inner-icon="streamline-emojis:bug" label="이체 딜레이 선택"
                        item-title="title" item-value="id" single-line :readonly="is_readonly_fin_trx_delay"/>
                    <VTooltip activator="parent" location="top">
                        사고 방지를 위해 결제모듈이 최초거래가 발생한 순간부터 이체 딜레이를 수정할 수 없습니다.
                    </VTooltip>
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
