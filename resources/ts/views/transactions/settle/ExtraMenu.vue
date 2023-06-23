<script setup lang="ts">
import { SettleMerchandise } from '@/views/types'
import { requiredValidator } from '@validators';
import { axios } from '@axios';

interface Props {
    id: number,
    name: string,
    item: SettleMerchandise,
    is_mcht: boolean
}

const props = defineProps<Props>()
const store = <any>(inject('store'))
const alert = <any>(inject('alert'))
const snackbar = <any>(inject('snackbar'))
const errorHandler = <any>(inject('$errorHandler'))
const visible = ref(false)
const settle_dt = ref()

const settleMcht = async () => {
    const page = props.is_mcht ? 'merchandises' : 'salesfoces';
    if (await alert.value.show('정말 ' + props.name + '을(를) 정산 하시겠습니까?')) {
        const params = {
            id: props.item.id,
            acct_nm: props.item.acct_nm,
            acct_num: props.item.acct_num,
            acct_bank_cd: props.item.acct_bank_cd,
            acct_bank_nm: props.item.acct_bank_nm,
            total_amount: props.item.amount,
            cxl_amount: props.item.cxl.amount,
            appr_amount: props.item.appr.amount,
            deduct_amount: props.item.deduction.amount,
            settle_amount: props.item.settle.amount,
            settle_dt: store.params.dt,
        };
        try {
            const r = await axios.post('/api/v1/manager/transactions/settle-histories/' + page, params)
            snackbar.value.show('성공하였습니다.', 'success')
            store.setTable()
        }
        catch (e: any) {
            snackbar.value.show(e.response.data.message, 'error')
            const r = errorHandler(e)
        }
    }
}
</script>
<template>
    <VBtn icon size="x-small" color="default" variant="text">
        <VIcon size="22" icon="tabler-dots-vertical" />
        <VMenu activator="parent">
            <VList>
                <VListItem value="settle" @click="settleMcht()">
                    <template #prepend>
                        <VIcon size="24" class="me-3" icon="tabler-calculator" />
                    </template>
                    <VListItemTitle>정산하기</VListItemTitle>
                </VListItem>
            </VList>
        </VMenu>
    </VBtn>

    <VDialog v-model="visible" persistent class="v-dialog-sm">
        <!-- Dialog close btn -->
        <DialogCloseBtn @click="visible = !visible" />
        <!-- Dialog Content -->
        <VCard title="정산일 설정">
            <VCardText>
                <AppDateTimePicker v-model="settle_dt" prepend-inner-icon="ic-baseline-calendar-today"
                    label="정산일" />
            </VCardText>
            <VCardText class="d-flex justify-end gap-3 flex-wrap">
                <VBtn color="secondary" variant="tonal" @click="visible = false;">
                    취소
                </VBtn>
                <VBtn @click="visible = false;">
                    동의
                </VBtn>
            </VCardText>
        </VCard>
    </VDialog>
</template>
