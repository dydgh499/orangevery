<script lang="ts" setup>
import { payWindowStore } from '@/views/quick-view/payWindowStore';
import { useRequestStore } from '@/views/request';
import { StatusColorSetter } from '@/views/searcher';
import { useStore } from '@/views/services/pay-gateways/useStore';
import type { VirtualAccount } from '@/views/types';
import { fin_trx_delays, withdraw_limit_types, withdraw_types } from '@/views/virtual-accounts/wallets/useStore';
import { isAbleModiyV2 } from '@axios';
import { VForm } from 'vuetify/components';
interface Props {
    item: VirtualAccount,
}
const vForm = ref<VForm>()
const props = defineProps<Props>()
const { copy } = payWindowStore()
const { update, remove } = useRequestStore()
const { finance_vans } = useStore()

const removeItem = () => {
    if(props.item.id)
        remove('/virtual-accounts/wallets', props.item, false)
    else 
        props.item.id = -1
}

</script>
<template>
    <VRow>
        <VCol cols="12">
            <div
                v-if="isAbleModiyV2(props.item, 'virtual-accounts/wallets')"
                icon
                class="action-btn edit-btn"
                @click="update('/virtual-accounts/wallets', props.item, vForm, false)"
            >
                <VIcon :icon="`tabler-pencil`" />
                <VTooltip activator="parent" location="top" transition="scale-transition">
                    {{ props.item.id === 0 ? "지갑생성" : "수정하기" }}
                </VTooltip>
            </div>
            <div
                v-if="isAbleModiyV2(props.item, 'virtual-accounts/wallets')"
                icon
                class="action-btn remove-btn"
                @click="removeItem()" 
            >
                <VIcon :icon="`tabler-x`" />
                <VTooltip activator="parent" location="top" transition="scale-transition">
                    {{ "삭제하기" }}
                </VTooltip>
            </div>
            <VCard>
                <VForm ref="vForm">
                    <VCardSubtitle style="margin-top: 0.5em;">
                        <VChip variant="outlined">지갑정보</VChip>
                    </VCardSubtitle>
                    <VCol v-if="props.item.id">
                        <VRow>
                            <VCol cols="8" md="7" class="small-font">
                                <VRow>
                                    <VCol md="5">
                                        <b>지갑코드</b>
                                    </VCol>
                                    <VCol md="7">
                                        <VChip v-if="props.item.id" color="success">
                                            {{ props.item.account_code }}
                                        </VChip>
                                    </VCol>
                                </VRow>
                            </VCol>
                            <VCol cols="4" md="5" class="small-font" style="text-align: end;">
                                <VBtn 
                                    type="button" 
                                    color="primary" 
                                    size="small" 
                                    variant="tonal"
                                    style="margin-top: auto;"
                                    @click="copy(props.item.account_code, '지갑코드')">
                                    코드복사
                                </VBtn>
                            </VCol>
                        </VRow>
                    </VCol>
                    <VCol>
                        <VRow>
                            <VCol
                                v-if="props.item.id" 
                                cols="12" md="6" class="small-font" >
                                <VRow>
                                    <VCol md="6">
                                        <b>현재잔액</b>

                                    </VCol>
                                    <VCol md="6" style="text-align: end;">
                                        {{ props.item.balance?.toLocaleString() }}원
                                    </VCol>
                                </VRow>
                            </VCol>
                            <VCol cols="12" md="6" class="small-font">
                                <VTextField 
                                        v-if="isAbleModiyV2(props.item, 'virtual-accounts/wallets')"
                                        v-model="props.item.account_name" 
                                        label="지갑 별칭" 
                                        variant="underlined"
                                    />
                                <VRow v-else>
                                    <VCol cols="12" md="6">
                                        <b>지갑 별칭</b>
                                    </VCol>
                                    <VCol cols="12" md="6" style="text-align: end;">
                                        {{ props.item.account_name }}
                                    </VCol>
                                </VRow>
                            </VCol>
                        </VRow>
                    </VCol>
                    <VDivider style="margin-bottom: 0.5em;"/>
                    <VCardSubtitle>
                        <VChip variant="outlined">출금정보</VChip>
                    </VCardSubtitle>
                    <VCol>
                        <VRow>
                            <VCol 
                                v-if="isAbleModiyV2(props.item, 'virtual-accounts/wallets')"
                                cols="12" md="6" 
                                class="small-font"
                            >
                                <VAutocomplete 
                                    :menu-props="{ maxHeight: 400 }" 
                                    v-model="props.item.fin_id" 
                                    :items="finance_vans"
                                    label="이체모듈 타입" 
                                    item-title="nick_name" 
                                    item-value="id" 
                                    variant="underlined"
                                />
                            </VCol>
                            <VCol cols="12" md="6" class="small-font">
                                <VAutocomplete 
                                    v-if="isAbleModiyV2(props.item, 'virtual-accounts/wallets')"
                                    :menu-props="{ maxHeight: 400 }" 
                                    v-model="props.item.fin_trx_delay" 
                                    :items="fin_trx_delays"
                                    label="출금딜레이" 
                                    item-title="title" 
                                    item-value="id" 
                                    variant="underlined"
                                />
                                <VRow v-else>
                                    <VCol cols="12" md="6">
                                        <b>출금딜레이</b>
                                    </VCol>
                                    <VCol cols="12" md="6" style="text-align: end;">
                                        {{ fin_trx_delays.find(obj => obj.id === props.item.fin_trx_delay)?.title }}
                                    </VCol>
                                </VRow>
                            </VCol>
                            <VCol cols="12" md="6" class="small-font">
                                <VAutocomplete 
                                    v-if="isAbleModiyV2(props.item, 'virtual-accounts/wallets')"
                                    :menu-props="{ maxHeight: 400 }" 
                                    v-model="props.item.withdraw_type" 
                                    :items="withdraw_types"
                                    label="출금타입" 
                                    item-title="title" 
                                    item-value="id" 
                                    variant="underlined"
                                />
                                <VRow v-else>
                                    <VCol cols="12" md="6">
                                        <b>출금타입</b>
                                    </VCol>
                                    <VCol cols="12" md="6" style="text-align: end;">
                                        <VChip :color="StatusColorSetter().getSelectIdColor(props.item.withdraw_type)">
                                            {{ withdraw_types.find(obj => obj.id === props.item.withdraw_type)?.title }}
                                        </VChip>
                                    </VCol>
                                </VRow>
                            </VCol>
                            
                            <VCol cols="12" md="6" class="small-font">
                                
                                <VTextField 
                                    v-if="isAbleModiyV2(props.item, 'virtual-accounts/wallets')"
                                    prepend-inner-icon="tabler-currency-won"
                                    v-model="props.item.withdraw_fee" 
                                    type="number" 
                                    suffix="원" 
                                    label="출금 수수료"
                                    variant="underlined"
                                />
                                <VRow v-else>
                                    <VCol cols="12" md="6">
                                        <b>출금 수수료</b>
                                    </VCol>
                                    <VCol cols="12" md="6" style="text-align: end;">
                                        {{ props.item.withdraw_fee }} 원
                                    </VCol>
                                </VRow>
                            </VCol>
                        </VRow>
                    </VCol>
                    <VDivider style="margin-bottom: 0.5em;"/>
                    <VCardSubtitle>
                        <VChip variant="outlined">출금제한</VChip>
                    </VCardSubtitle>
                    <VCol>
                        <VRow    
                        v-if="isAbleModiyV2(props.item, 'virtual-accounts/wallets')" >
                            <VCol
                                cols="12" md="12" class="small-font">
                                <VAutocomplete 
                                    :menu-props="{ maxHeight: 400 }" 
                                    v-model="props.item.withdraw_limit_type" 
                                    :items="withdraw_limit_types"
                                    label="출금제한타입" 
                                    item-title="title" 
                                    item-value="id" 
                                    variant="underlined"
                                />
                            </VCol>
                        </VRow>
                        <VRow v-else>
                            <VCol cols="12" md="6">
                                <b>출금제한타입</b>
                            </VCol>
                            <VCol cols="12" md="6" style="text-align: end;">
                                <VChip :color="StatusColorSetter().getSelectIdColor(props.item.withdraw_limit_type || 0)">
                                    {{ withdraw_limit_types.find(obj => obj.id === props.item.withdraw_limit_type)?.title }}
                                </VChip>
                            </VCol>
                        </VRow>
                    </VCol>
                    <VCol>
                        <VRow v-if="isAbleModiyV2(props.item, 'virtual-accounts/wallets')">
                            <VCol cols="12" md="6">
                                <VTextField 
                                    prepend-inner-icon="tabler-currency-won"
                                    v-model="props.item.withdraw_business_limit" 
                                    type="number" 
                                    suffix="만원" 
                                    label="일 출금한도(영업일)"
                                    variant="underlined"
                                />
                            </VCol>
                            <VCol cols="12" md="6">
                                <VTextField 
                                    prepend-inner-icon="tabler-currency-won"
                                    v-model="props.item.withdraw_holiday_limit" 
                                    type="number" 
                                    suffix="만원" 
                                    label="일 출금한도(휴무일)"
                                    variant="underlined"
                                />
                            </VCol>
                        </VRow>
                        <VRow v-else>
                            <VCol cols="12" md="6">
                                <b>일 출금한도(영업일)</b>
                            </VCol>
                            <VCol cols="12" md="6" style="text-align: end;">
                                {{ props.item.withdraw_business_limit }} 만원
                            </VCol>
                            <VCol cols="12" md="6">
                                <b>일 출금한도(휴무일)</b>
                            </VCol>
                            <VCol cols="12" md="6" style="text-align: end;">
                                {{ props.item.withdraw_business_limit }} 만원
                            </VCol>
                        </VRow>
                    </VCol>
                </VForm>
            </VCard>
        </VCol>
    </VRow>
</template>
<style scoped lang="scss">
:deep(.v-input--density-compact .v-field--variant-plain, .v-input--density-compact .v-field--variant-underlined) {
  --v-input-control-height: 34px !important;
}

.action-btn {
  position: absolute;
  z-index: 9;
  border-radius: 0.2rem;
  block-size: 1.6em;
  color: rgba(var(--v-theme-background)) !important;
  cursor: pointer;
  inline-size: 1.6em;
  margin-block-start: -0.5em;
  text-align: center;
  transform: scale(1);
  transition: 0.5s ease all;

  /* 기본 상태에서의 transition 설정 */

  &:hover {
    color: rgba(var(--v-theme-surface)) !important;
    transform: scale(1.1);

    /* 회전과 크기 조정 함께 적용 */
    transform-origin: center;

    /* 회전 중심 설정 */
  }
}

.edit-btn {
  background-color: rgba(var(--v-theme-primary), 1) !important;
  inset-inline-end: 3em;
}

.remove-btn {
  background-color: rgba(var(--v-theme-error), 1) !important;
  inset-inline-end: 1em;
}
</style>
