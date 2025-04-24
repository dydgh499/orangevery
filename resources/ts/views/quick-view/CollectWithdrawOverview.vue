<script setup lang="ts">
import { inputFormater } from '@/@core/utils/formatters';
import SkeletonBox from '@/layouts/utils/SkeletonBox.vue';
import { useRequestStore } from '@/views/request';
import { VirtualAccount } from '../types';

interface Props {
    is_skeleton: boolean,
    virtual_account: VirtualAccount,
}

const props = defineProps<Props>()
const { get, post } = useRequestStore()
const {
    amount_format,
    amount,
    formatAmount
} = inputFormater()
const able_balance = ref(0)

const alert = <any>(inject('alert'))
const snackbar = <any>(inject('snackbar'))

const getWithdrawAbleAmount = async() => {
    const r = await get('/api/v1/quick-view/withdraws/balance', {
        params: {
            va_id: props.virtual_account.id
        }
    })
    able_balance.value = (Number(r.data.profit) - r.data.withdraw_fee) || 0
}

const requestWithdraw = async() => {
    if(able_balance.value >= amount.value) {
        if(amount.value) {
            if(await alert.value.show('정말 '+amount.value+'원을 출금하시겠습니까?')) {
                const r = await post('/api/v1/quick-view/withdraws/collect', {
                    va_id: props.virtual_account.id,
                    withdraw_amount: amount.value,
                })
            }
        }
        else
            snackbar.value.show('출금금액을 입력해주세요.', 'warning')
    }
    else
        snackbar.value.show('출금가능 금액을 초과하였습니다.', 'warning')
}

watchEffect(() => {
    getWithdrawAbleAmount()
})
</script>
<template>
    <VCol>
        <VRow no-gutters style="align-items: center;">
            <VCol class="small-font">
                <span class="text-primary">출금</span>가능 금액   
                <VBtn variant="text" color="secondary" size="small" icon @click="getWithdrawAbleAmount()">
                    <VIcon icon="tabler:refresh" size="20" />
                </VBtn>
            </VCol>
            <VCol style="text-align: end;">
                <VChip color="success">
                    {{ props.virtual_account.account_name}}
                </VChip>
            </VCol>
        </VRow>
        <VRow no-gutters style=" align-items: center;font-weight: bold;">
            <VCol>
                <SkeletonBox v-if="props.is_skeleton" :width="'8em'"/>
                <b v-else>
                    {{ able_balance.toLocaleString() + " 원"}}
                </b>
            </VCol>
        </VRow>
    </VCol>
    <VDivider/>
    <VCol>
        <VRow no-gutters style="align-items: center;">
            <VCol class="small-font">
                <span class="text-primary">출금</span>금액 입력
            </VCol>
        </VRow>
        <VRow no-gutters style=" align-items: center;font-weight: bold;">
            <VCol>
                <SkeletonBox v-if="props.is_skeleton" :width="'10em'" :height="'2em'"/>
                <VTextField 
                    v-else
                    v-model="amount_format" 
                    @input="formatAmount"
                    variant="underlined"
                    suffix="￦" 
                    placeholder="출금금액 입력"
                    style="min-width: 10em;"/>
            </VCol>
            <VCol style="text-align: end;">
                <VBtn color="primary" type="button" @click="requestWithdraw()" style="margin-top: auto;">
                    출금하기
                </VBtn>
            </VCol>
        </VRow>
    </VCol>
</template>
<style scoped lang="scss">
/* stylelint-disable-next-line selector-pseudo-class-no-unknown */
:deep(.v-input--density-compact .v-field--variant-plain, .v-input--density-compact .v-field--variant-underlined) {
  --v-input-control-height: 34px !important;
}
</style>
