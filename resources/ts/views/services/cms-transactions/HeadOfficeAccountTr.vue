<script lang="ts" setup>
import type { HeadOffceAccount } from '@/views/types'
import { banks } from '@/views/users/useStore'

interface Props {
    item: HeadOffceAccount,
    index: number,
}
const props = defineProps<Props>()

const setAcctBankName = () => {
    const bank = banks.find(obj => obj.code == props.item.acct_bank_code)
    props.item.acct_bank_name = bank ? bank.title : '선택안함'
}
</script>
<template>
    <tr>
        <td style="width: 5%;">{{ index + 1 }}</td>
        <td style="width: 20%;">
            <span>{{ props.item.acct_num }}</span>
        </td>
        <td style="width: 15%;">
            <span>{{ props.item.acct_name }}</span>
        </td>
        <td style="width: 20%;">
            <span>{{ props.item.acct_bank_name }}</span>
        </td>
        <td style="width: 15%;">
            <span>{{ props.item.acct_bank_code }}</span>
        </td>
    </tr>
</template>
<style scoped>
td {
  padding: 0 !important;
}
</style>
