<script lang="ts" setup>
import BaseQuestionTooltip from '@/layouts/tooltips/BaseQuestionTooltip.vue'
import HeadOfficeAccountTr from '@/views/services/head-office-withdraw/HeadOfficeAccountTr.vue'
import { useHeadOfficeAccountStore } from '@/views/services/head-office-withdraw/useStore'
import type { HeadOffceAccount } from '@/views/types'
import { useRequestStore } from '@/views/request'

const { setNullRemove } = useRequestStore()
const { head_office_accounts } = useHeadOfficeAccountStore()

const new_head_office_accounts = reactive<HeadOffceAccount[]>([])

const addNewHeadOffceAccount = () => {
    const head_office_account = <HeadOffceAccount>({
        id: 0,
        acct_bank_name: '은행명',
    })
    new_head_office_accounts.push(head_office_account)
}
watchEffect(() => {
    setNullRemove(head_office_accounts)
    setNullRemove(new_head_office_accounts)
})
</script>
<template>
    <VCardTitle>
        <BaseQuestionTooltip :location="'top'" :text="'본사 지정계좌 등록'" :content="'출금에 사용할 지정계좌를 등록합니다.'">
        </BaseQuestionTooltip>
    </VCardTitle>
    <VDivider style="margin-top: 1em;" />
    <VTable style="width: 100%;margin-bottom: 1em;text-align: center;">
        <thead>
            <tr>
                <th scope="col" style="text-align: center;">No.</th>
                <th scope="col" style="text-align: center;">계좌번호</th>
                <th scope="col" style="text-align: center;">예금주</th>
                <th scope="col" style="text-align: center;">출금은행</th>
                <th scope="col" style="text-align: center;"></th>
            </tr>
        </thead>
        <tbody>
            <HeadOfficeAccountTr v-for="(item, index) in head_office_accounts"
                :key="item.id" style="margin-top: 1em;" :item="item" :index="index" />
            <HeadOfficeAccountTr v-for="(item, index) in new_head_office_accounts" :key="index"
                style="margin-top: 1em;" :item="item" :index="(index+head_office_accounts.length)" />
        </tbody>
        <tfoot v-show="Boolean(head_office_accounts.length + new_head_office_accounts.length == 0)">
            <tr>
                <td colspan="4" class="text-center">
                    지정계좌를 등록해주세요.
                </td>
            </tr>
        </tfoot>
    </VTable>
    <VRow>
        <VCol class="d-flex gap-4">
            <VBtn type="button" style="margin-left: auto;" @click="addNewHeadOffceAccount()">
                지정계좌 신규추가
                <VIcon end icon="tabler-plus" />
            </VBtn>
        </VCol>
    </VRow>
</template>
