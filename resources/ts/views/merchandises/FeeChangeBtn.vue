<script setup lang="ts">
import { axios } from '@axios';
import type { Merchandise } from '@/views/types'

interface Props {
    level: number,
    item: Merchandise,
}
const props = defineProps<Props>()

const alert = <any>(inject('alert'))
const snackbar = <any>(inject('snackbar'))
const errorHandler = <any>(inject('$errorHandler'))

const getSalesByClass = () => {
    if (props.level == 0) {
        return {
            sales_id: props.item.sales0_id,
            sales_fee: parseFloat(props.item.sales0_fee),
        }
    }
    else if (props.level == 1) {
        return {
            sales_id: props.item.sales1_id,
            sales_fee: parseFloat(props.item.sales1_fee),
        }
    }
    else if (props.level == 2) {
        return {
            sales_id: props.item.sales2_id,
            sales_fee: parseFloat(props.item.sales2_fee),
        }
    }
    else if (props.level == 3) {
        return {
            sales_id: props.item.sales3_id,
            sales_fee: parseFloat(props.item.sales3_fee),
        }
    }
    else if (props.level == 4) {
        return {
            sales_id: props.item.sales4_id,
            sales_fee: parseFloat(props.item.sales4_fee),
        }
    }
    else if (props.level == 5) {
        return {
            sales_id: props.item.sales5_id,
            sales_fee: parseFloat(props.item.sales5_fee),
        }
    }
    else
        return {}
}

const feeChangeRequest = async (type: string) => {
    let url = '/api/v1/manager/merchandises/fee-change-histories'
    let params = {}
    if (props.level < 0) {
        params = {
            trx_fee: parseFloat(props.item.trx_fee),
            hold_fee: parseFloat(props.item.hold_fee),
            mcht_id: props.item.id,
        }
        url += '/merchandises/' + type
    }
    else {
        params = Object.assign({
            class: props.level,
            mcht_id: props.item.id,
        }, getSalesByClass())
        url += '/salesforces/' + type
    }

    try {
        const r = await axios.post(url, params)
        snackbar.value.show('성공하였습니다.', 'success')
    }
    catch (e: any) {
        snackbar.value.show(e.response.data.message, 'error')
        const r = errorHandler(e)
    }
}
const directFeeChange = async () => {
    if (await alert.value.show('정말 즉시적용하시겠습니까?'))
        await feeChangeRequest('direct-apply')
}
const bookFeeChange = async () => {
    if (await alert.value.show('정말 예약적용하시겠습니까? 명일 00시에 반영됩니다.'))
        await feeChangeRequest('book-apply')
}
</script>
<template>
    <VCol cols="12" md="2" style="display: flex; flex-direction: row; justify-content: space-between;">
        <VBtn size="small" variant="tonal" @click="directFeeChange()" style='flex-grow: 1; margin: 0.25em 0.5em;'>
            즉시적용
            <VIcon end icon="tabler-direction-sign" />
        </VBtn>
        <VBtn size="small" variant="tonal" color="secondary" @click="bookFeeChange()"
            style='flex-grow: 1; margin: 0.25em 0.5em;'>
            예약적용
            <VIcon end icon="tabler-clock-up" />
        </VBtn>
    </VCol>
</template>
