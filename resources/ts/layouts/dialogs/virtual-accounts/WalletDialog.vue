<script setup lang="ts">
import { getUserLevel } from '@/plugins/axios';
import { useRequestStore } from '@/views/request';
import type { VirtualAccount } from '@/views/types';
import WalletItem from '@/views/virtual-accounts/wallets/WalletItem.vue';

const { setNullRemove } = useRequestStore()
const visible = ref(false)
const user_id = ref(<number>(0))
const virtual_accounts = ref(<VirtualAccount[]>([]))

const addNewVirtualAccount = () => {
    virtual_accounts.value.push(<VirtualAccount>({
        id: 0,
        user_id: user_id.value,
        level: 10,
        balance: 0,
        account_code: '',
        account_name: '',
        fin_id: null,
        fin_trx_delay: 0,
        withdraw_type: 0,
        withdraw_fee: 0,
        withdraw_limit_type: 0,
        withdraw_business_limit: 0,
        withdraw_holiday_limit: 0,
    }))
    
}
watchEffect(() => {
    if(virtual_accounts.value && virtual_accounts.value.length)
        setNullRemove(virtual_accounts.value)
})

const show = (_user_id: number, _virtual_accounts :VirtualAccount[]) => {
    user_id.value = _user_id
    virtual_accounts.value = _virtual_accounts
    visible.value = true
}

defineExpose({
    show
});
</script>
<template>
    <VDialog v-model="visible" max-width="450">
        <DialogCloseBtn @click="visible = false" />
        <VCard>
            <VCardTitle>
                <div style="display: flex;align-items: center;justify-content: space-between;">
                    <VCardTitle style="margin-right: 1em;">지갑 관리</VCardTitle>
                    <VBtn type="button" 
                        v-if="getUserLevel() >= 35 && user_id"
                        size="small"
                        style="margin-left: auto;" 
                        @click="addNewVirtualAccount()">
                        추가하기
                    </VBtn>
                </div>
            </VCardTitle>
            <VCardText>
                <WalletItem 
                v-for="(virtual_account, index) in virtual_accounts"
                        :key="virtual_account.id" 
                        :item="virtual_account" 
                        />
            </VCardText>
        </VCard>
    </VDialog>
</template>
<style scoped>
/* stylelint-disable-next-line selector-pseudo-class-no-unknown */
:deep(.v-row) {
  align-items: center;
}

/* stylelint-disable-next-line selector-pseudo-class-no-unknown */
:deep(.v-table__wrapper) {
  block-size: auto !important;
}
</style>
