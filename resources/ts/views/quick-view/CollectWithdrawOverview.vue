<script setup lang="ts">
import { inputFormater } from '@/@core/utils/formatters';
import SkeletonBox from '@/layouts/utils/SkeletonBox.vue';
import { getUserLevel, user_info } from '@/plugins/axios';
import { useRequestStore } from '@/views/request';

interface Props {
    is_skeleton: boolean,
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
    const r = await get('/api/v1/quick-view/collect-withdraws/balance', {})
    able_balance.value = (Number(r.data.profit) - r.data.withdraw_fee) || 0
}

const requestWithdraw = async() => {
    if(able_balance.value >= amount.value) {
        if(amount.value) {
            if(await alert.value.show('정말 '+amount.value+'원을 출금하시겠습니까?')) {
                const r = await post('/api/v1/quick-view/collect-withdraws', {
                    withdraw_amount: amount.value,
                })
                if(r.status == 201)
                    getWithdrawAbleAmount()
            }
        }
        else
            snackbar.value.show('출금금액을 입력해주세요.', 'warning')
    }
    else
        snackbar.value.show('출금가능 금액을 초과하였습니다.', 'warning')
}

watchEffect(() => {
    if(getUserLevel() == 10 && user_info.value.use_collect_withdraw)
        getWithdrawAbleAmount()
})
</script>
<template>
    <VCol class="d-flex justify-space-between small-font">
        <div>
            <div class="small-font">
                <span class="text-info">출금</span>가능 금액                    
            </div>
            <div style="font-weight: bold;">
                <SkeletonBox v-if="props.is_skeleton" :width="'8em'"/>
                <span v-else :class="able_balance ? 'text-success' : 'text-error'">
                    {{ able_balance.toLocaleString() }}
                </span>
                원
            </div>
        </div>
    </VCol>
    <VCol style="padding-top: 0;" class="d-flex justify-space-between small-font">
        <div>
            <div class="small-font">
                <span class="text-info">출금</span>금액 입력
            </div>
            <div>
                <SkeletonBox v-if="props.is_skeleton" :width="'10em'" :height="'2em'"/>
                <VTextField 
                    v-else
                    v-model="amount_format" 
                    @input="formatAmount"
                    variant="underlined"
                    suffix="￦" 
                    placeholder="출금금액 입력"
                    style="min-width: 10em;"/>
            </div>
        </div>
        <VBtn size="small" color="info" type="button" @click="requestWithdraw()" style="margin-top: auto;">
            출금하기
        </VBtn>
    </VCol>
</template>
<style scoped lang="scss">
:deep(.v-input--density-compact .v-field--variant-plain, .v-input--density-compact .v-field--variant-underlined) {
  --v-input-control-height: 34px !important;
}
</style>
