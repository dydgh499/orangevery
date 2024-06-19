<script lang="ts" setup>
import { useRequestStore } from '@/views/request'
import type { HeadOffceAccount } from '@/views/types'
import { banks } from '@/views/users/useStore'
import { requiredValidatorV2 } from '@validators'
import { VForm } from 'vuetify/components'

interface Props {
    item: HeadOffceAccount,
    index: number,
}
const vForm = ref<VForm>()
const props = defineProps<Props>()
const { update, remove } = useRequestStore()

const setAcctBankName = () => {
    const bank = banks.find(obj => obj.code == props.item.acct_bank_code)
    props.item.acct_bank_name = bank ? bank.title : '선택안함'
}
</script>
<template>
    <tr>
        <td style="width: 5%;">{{ index + 1 }}</td>
        <td style="width: 20%;">
            <VForm ref="vForm">
                    <VTextField v-model="props.item.acct_num" prepend-inner-icon="ri-bank-card-fill" placeholder="계좌번호 입력"
                        persistent-placeholder style="margin: 0 0.5em;"/>
            </VForm>
        </td>
        <td style="width: 15%;">
            <VForm ref="vForm">
                    <VTextField v-model="props.item.acct_name" prepend-inner-icon="tabler-user" placeholder="예금주 입력"
                        persistent-placeholder style="margin: 0 0.5em;" />
            </VForm>
        </td>
        <td style="width: 20%;">
            <VForm ref="vForm">
                <br>
                    <VAutocomplete :menu-props="{ maxHeight: 400 }" v-model="props.item.acct_bank_code"
                        :items="[{ code: null, title: '선택안함' }].concat(banks)" prepend-inner-icon="ph-buildings"
                        label="은행 선택"
                        :hint="`${props.item.acct_bank_name}, 은행 코드: ${props.item.acct_bank_code ? props.item.acct_bank_code : '000'} `"
                        item-title="title" item-value="code" persistent-hint single-line :rules="[requiredValidatorV2(props.item.acct_bank_code, '은행정보')]" create
                        @update:modelValue="setAcctBankName()" style="margin: 0 0.5em;" />
            </VForm>
        </td>
    </tr>
</template>
<style scoped>
td {
  padding: 0 !important;
}
</style>
